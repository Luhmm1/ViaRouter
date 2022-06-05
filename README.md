<h1 align="center">ViaRouter</h1>

<p align="center">A middleware version of FastRoute.</p>

<p align="center">
  <a href="https://github.com/Luhmm1/ViaRouter/actions/workflows/tests.yml">
    <img src="https://github.com/Luhmm1/ViaRouter/actions/workflows/tests.yml/badge.svg" alt="Tests">
  </a>
  <a href="https://packagist.org/packages/luhmm1/viarouter">
    <img src="https://flat.badgen.net/packagist/v/luhmm1/viarouter" alt="Version">
  </a>
  <a href="https://www.php.net/">
    <img src="https://flat.badgen.net/packagist/php/luhmm1/viarouter" alt="PHP">
  </a>
  <a href="https://github.com/Luhmm1/ViaRouter/blob/master/LICENSE">
    <img src="https://flat.badgen.net/packagist/license/luhmm1/viarouter" alt="License">
  </a>
</p>

## Installation

### 1. Prerequisites

- PHP (^8.0)
- Composer
- A middleware dispatcher (PSR-15)
- [PHP-DI](https://php-di.org/)

### 2. Install the library

You can install the library using this Composer command:

```
composer require luhmm1/viarouter
```

## Usage

To use the library, add to your middleware dispatcher a new instance of `\Luhmm1\ViaRouter\ViaRouterMiddleware`.

This instance must have 2 parameters:

- The first parameter must be an instance of `\DI\Container`. This instance will allow you to inject the objects you want, directly into the parameters of your methods.
- The second parameter must be an array of your controllers.

Application.php:

```php
use DI\ContainerBuilder;
use Luhmm1\ViaRouter\ViaRouterMiddleware;
use Twig\Environment;

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    Environment::class => function (): Environment {
        // Initialization of Twig.
    }
]);

$container = $containerBuilder->build();

(new DummyMiddlewareDispatcher([
    new ViaRouterMiddleware($container, [
        DefaultController::class
    ])
]))->dispatch();
```

DefaultController.php:

```php
use Luhmm1\ViaRouter\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;

class DefaultController
{
    #[Route('/[{name}]', ['GET'])]
    public function index(
      ResponseInterface $response,
      Environment $twig,
      ?string $name
    ): ResponseInterface {
        $response->getBody()->write($twig->render('homepage.twig', [
            'name' => $name
        ]));

        return $response;
    }
}
```

## Configuration

You can change some options (especially concerning the cache) by passing a third parameter to your `\Luhmm1\ViaRouter\ViaRouterMiddleware` instance.

This third parameter must be the same options array that can be passed to [FastRoute dispatchers](https://github.com/nikic/FastRoute#caching).
