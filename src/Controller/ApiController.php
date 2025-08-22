<?php

declare(strict_types=1);

namespace App\Controller;

use App\Security\TokenAuthenticator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * API Controller.
 */
class ApiController extends AbstractController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function checkAuthorization(Request $request): void
    {
        // TODO Extract TokenAuthenticator into ApiController
        $tokenAuthenticator = $this->container->get(TokenAuthenticator::class);
        $token = $this->extractBearerToken($request);

        if ($token === null) {
            throw new UnauthorizedHttpException('Bearer', 'Authorization header missing or malformed');
        }

        if (!$tokenAuthenticator->authenticate($token)) {
            throw new UnauthorizedHttpException('Bearer', 'Access token is invalid or expired');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function parseJsonData(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        if (!\is_array($data)) {
            throw new BadRequestHttpException('Invalid JSON data');
        }

        return $data;
    }

    private function extractBearerToken(Request $request): ?string
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        return str_replace('Bearer ', '', $authHeader);
    }
}
