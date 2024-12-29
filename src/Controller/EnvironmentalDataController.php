<?php

namespace App\Controller;

use App\Service\EnvironmentalDataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for handling environmental data operations.
 */
class EnvironmentalDataController extends AbstractController
{
    private EnvironmentalDataService $service;

    public function __construct(EnvironmentalDataService $service)
    {
        $this->service = $service;
    }

    /**
     * Handles the saving of environmental data from an API request.
     *
     * This method processes a POST request containing environmental data in JSON format,
     * decodes the data, and forwards it to the service layer for persistence. If the
     * operation fails, an error response is returned; otherwise, a success message is sent.
     *
     * @param Request $request The HTTP request containing JSON-encoded environmental data.
     *
     * @return JsonResponse A JSON response indicating success or failure of the data saving operation.
     */
    #[Route('/api/data', name: 'api_data', methods: ['POST'])]
    public function saveData(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $result = $this->service->saveEnvironmentalData($data);

        if (!$result['success']) {
            return new JsonResponse(['error' => $result['message']], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => 'Data saved successfully'], Response::HTTP_CREATED);
    }
}