<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\EnvironmentalDataService;
use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Environmental Data API Controller.
 */
class EnvironmentalDataApiController extends ApiController
{
    public function __construct(private readonly EnvironmentalDataService $service)
    {
    }

    #[Route('/api/environmental-data', name: 'api_environmental_data', methods: ['POST'])]
    public function saveData(Request $request): JsonResponse
    {
        $this->checkAuthorization($request);
        $data = $this->parseJsonData($request);

        try {
            foreach ($data as $row) {
                $this->service->saveEnvironmentalData($row);
            }

            return $this->json(['message' => 'Data saved successfully'], Response::HTTP_CREATED);
        } catch (InvalidArgumentException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
