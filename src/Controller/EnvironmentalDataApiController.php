<?php

declare(strict_types=1);

namespace App\Controller;

use App\Security\TokenAuthenticator;
use App\Service\EnvironmentalDataApiService;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Environmental Data API Controller.
 */
class EnvironmentalDataApiController extends AbstractController
{
    private EnvironmentalDataApiService $service;

    private TokenAuthenticator $tokenAuthenticator;

    public function __construct(EnvironmentalDataApiService $service, TokenAuthenticator $tokenAuthenticator)
    {
        $this->service = $service;
        $this->tokenAuthenticator = $tokenAuthenticator;
    }

    #[Route('/api/environmental-data', name: 'api_environmental_data', methods: ['POST'])]
    public function saveData(Request $request): JsonResponse
    {
        $token = $this->extractBearerToken($request);

        if ($token === null) {
            return $this->json(['error' => 'Missing or invalid Authorization header'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->tokenAuthenticator->authenticate($token)) {
            return $this->json(['error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        $data = $this->parseJsonData($request);

        if ($data === null) {
            return $this->json(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->service->saveEnvironmentalData($data);

            return $this->json(['message' => 'Data saved successfully'], Response::HTTP_CREATED);
        } catch (InvalidArgumentException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
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

    /**
     * @return array<string, mixed>|null
     */
    private function parseJsonData(Request $request): ?array
    {
        $data = json_decode($request->getContent(), true);

        return \is_array($data) ? $data : null;
    }
}
