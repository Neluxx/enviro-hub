<?php

namespace App\Controller;

use App\Service\EnvironmentalDataService;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidatorException;

class EnvironmentalDataController extends AbstractController
{
    public function __construct(private readonly EnvironmentalDataService $service)
    {
    }

    #[Route('/api/data', name: 'api_data', methods: ['POST'])]
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
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (ValidatorException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}