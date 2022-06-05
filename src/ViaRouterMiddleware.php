<?php

namespace Luhmm1\ViaRouter;

use DI\Container;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use InvalidArgumentException;
use Luhmm1\HttpExceptions\HttpMethodNotAllowedException;
use Luhmm1\HttpExceptions\HttpNotFoundException;
use Luhmm1\ViaRouter\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;

use function FastRoute\cachedDispatcher;

class ViaRouterMiddleware implements MiddlewareInterface
{
    private Container $container;

    /**
     * @var class-string[]
     */
    private array $controllers;

    /**
     * @var array<string, mixed>
     */
    private array $settings;

    /**
     * @param class-string[] $controllers
     * @param array<string, mixed> $settings
     */
    public function __construct(Container $container, array $controllers, array $settings = [])
    {
        $this->container = $container;
        $this->controllers = $controllers;

        $this->settings = array_merge([
            'cacheDisabled' => true,
            'cacheFile' => ''
        ], $settings);
    }

    /**
     * @throws HttpNotFoundException
     * @throws HttpMethodNotAllowedException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->getDispatcher()->dispatch($request->getMethod(), $request->getUri()->getPath());

        if ($route[0] === Dispatcher::NOT_FOUND) {
            throw new HttpNotFoundException();
        }

        if ($route[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            throw new HttpMethodNotAllowedException();
        }

        $this->container->set(ServerRequestInterface::class, $request);
        $this->container->set(ResponseInterface::class, $handler->handle($request));

        $response = $this->container->call($route[1], $route[2]);

        if (!$response instanceof ResponseInterface) {
            throw new InvalidArgumentException('Your method must return an instance of ResponseInterface.');
        }

        return $response;
    }

    private function getDispatcher(): Dispatcher
    {
        return cachedDispatcher(function (RouteCollector $r): void {
            foreach ($this->controllers as $controller) {
                $class = new ReflectionClass($controller);
                $methods = $class->getMethods();

                foreach ($methods as $method) {
                    $routeAttributes = $method->getAttributes(Route::class);

                    if ($routeAttributes !== []) {
                        /** @var Route $route */
                        $route = $routeAttributes[0]->newInstance();

                        foreach ($route->getMethods() as $httpMethod) {
                            $r->addRoute($httpMethod, $route->getPath(), [$class->getName(), $method->getName()]);
                        }
                    }
                }
            }
        }, $this->settings);
    }
}
