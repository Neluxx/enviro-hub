<?php

declare(strict_types=1);

namespace App\Controller;

use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * API Controller.
 */
class ApiController extends AbstractController
{
    public function checkAuthorization(Request $request): void
    {
        $token = $this->extractBearerToken($request);

        if ($token === null) {
            throw new UnauthorizedHttpException('Bearer', 'Authorization header missing or malformed');
        }

        if ($token !== $this->getParameter('bearer_token')) {
            throw new UnauthorizedHttpException('Bearer', 'Access token is invalid or expired');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function parseJsonData(Request $request): array
    {
        try {
            $data = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

            if (!\is_array($data)) {
                throw new BadRequestHttpException('Invalid JSON data');
            }

            return $data;
        } catch (JsonException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
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
