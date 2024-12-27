<?php

namespace App\Controller;

use App\Service\EnvironmentalDataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class EnvironmentalDataController extends AbstractController
{
    private EnvironmentalDataService $dataService;

    public function __construct(EnvironmentalDataService $dataService)
    {
        $this->dataService = $dataService;
    }

    #[Route('/api/data', name: 'api_data', methods: ['POST'])]
    public function saveData(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !$this->isValidData($data)) {
            return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->dataService->saveEnvironmentalData($data);

        if ($result['success'] === false) {
            return new JsonResponse(['error' => $result['message']], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => 'Data saved successfully'], Response::HTTP_CREATED);
    }

    private function isValidData(array $data): bool
    {
        return isset($data['temperature'], $data['humidity'], $data['pressure'], $data['co2'], $data['created']);
    }
}