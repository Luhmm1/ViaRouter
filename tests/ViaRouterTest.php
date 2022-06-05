<?php

namespace Luhmm1\ViaRouter\Tests;

use DI\Container;
use Luhmm1\HttpExceptions\HttpMethodNotAllowedException;
use Luhmm1\HttpExceptions\HttpNotFoundException;
use Luhmm1\ViaRouter\Tests\Utils\DummyController;
use Luhmm1\ViaRouter\Tests\Utils\DummyService;
use Luhmm1\ViaRouter\ViaRouterMiddleware;
use Melody\Handlers\QueueMiddlewareDispatcher;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ViaRouterTest extends TestCase
{
    private static function getContainer(): Container
    {
        $container = new Container();

        $container->set(DummyService::class, function () {
            return new DummyService('Léo', 'DEVILLE');
        });

        return $container;
    }

    private static function getResponse(string $method, string $path): ResponseInterface
    {
        $psr17 = new Psr17Factory();
        $request = $psr17->createServerRequest($method, $path);

        $dispatcher = new QueueMiddlewareDispatcher($psr17);

        $dispatcher->addMiddleware(new ViaRouterMiddleware(self::getContainer(), [
            DummyController::class
        ]));

        return $dispatcher->handle($request);
    }

    public function testRouteFound(): void
    {
        $response = self::getResponse('GET', '/found');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Found!', (string) $response->getBody());
    }

    public function testRouteNotFound(): void
    {
        self::expectException(HttpNotFoundException::class);

        self::getResponse('GET', '/not-found');
    }

    public function testRouteMethodNotAllowed(): void
    {
        self::expectException(HttpMethodNotAllowedException::class);

        self::getResponse('POST', '/found');
    }

    public function testRouteWithCustomStatus(): void
    {
        $response = self::getResponse('GET', '/custom-status');

        self::assertSame(418, $response->getStatusCode());
    }

    public function testRouteWithPlaceholder(): void
    {
        $response = self::getResponse('GET', '/profiles/2');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('2', (string) $response->getBody());
    }

    public function testRouteWithPlaceholders(): void
    {
        $response = self::getResponse('GET', '/profiles/2/Luhmm1');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('2. Luhmm1', (string) $response->getBody());
    }

    public function testRouteWithPlaceholdersNotUsed(): void
    {
        $response = self::getResponse('GET', '/accounts/2/Luhmm1');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Nothing here!', (string) $response->getBody());
    }

    public function testRouteWithDummyService(): void
    {
        $response = self::getResponse('GET', '/dummy-service');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Léo DEVILLE', (string) $response->getBody());
    }
}
