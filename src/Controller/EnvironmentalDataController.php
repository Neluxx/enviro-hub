<?php

namespace App\Controller;

use App\Service\EnvironmentalDataService;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EnvironmentalDataController extends AbstractController
{
    private EnvironmentalDataService $service;

    public function __construct(EnvironmentalDataService $service)
    {
        $this->service = $service;
    }

    #[Route('/api/environmental-data', name: 'api_environmental_data', methods: ['POST'])]
    public function saveData(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
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
}