<?php

namespace Luhmm1\ViaRouter\Tests\Utils;

use Luhmm1\ViaRouter\Attributes\Route;
use Psr\Http\Message\ResponseInterface;

class DummyController
{
    #[Route('/found')]
    public function found(ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write('Found!');

        return $response;
    }

    #[Route('/custom-status')]
    public function customStatus(ResponseInterface $response): ResponseInterface
    {
        return $response->withStatus(418);
    }

    #[Route('/profiles/{id}')]
    public function placeholder(ResponseInterface $response, int $id): ResponseInterface
    {
        $response->getBody()->write((string) $id);

        return $response;
    }

    #[Route('/profiles/{id}/{username}')]
    public function placeholders(ResponseInterface $response, int $id, string $username): ResponseInterface
    {
        $response->getBody()->write((string) $id . '. ' . $username);

        return $response;
    }

    #[Route('/accounts/{id}/{username}')]
    public function placeholdersNotUsed(ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write('Nothing here!');

        return $response;
    }

    #[Route('/dummy-service')]
    public function dummyService(ResponseInterface $response, DummyService $dummyService): ResponseInterface
    {
        $response->getBody()->write($dummyService->getFullName());

        return $response;
    }
}
