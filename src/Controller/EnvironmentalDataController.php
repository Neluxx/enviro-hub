<?php

namespace App\Controller;

use App\Service\EnvironmentalDataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EnvironmentalDataController extends AbstractController
{
    private const ERROR_MESSAGE = 'Invalid data';
    private const SUCCESS_MESSAGE = 'Data saved successfully';

    private EnvironmentalDataService $service;

    public function __construct(EnvironmentalDataService $service)
    {
        $this->service = $service;
    }

    #[Route('/api/data', name: 'api_data', methods: ['POST'])]
    public function saveData(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !$this->service->hasAllRequiredFields($data)) {
            return new JsonResponse(['error' => self::ERROR_MESSAGE], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->service->saveEnvironmentalData($data);

        if (!$result['success']) {
            return new JsonResponse(['error' => $result['message']], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => self::SUCCESS_MESSAGE], Response::HTTP_CREATED);
    }
}