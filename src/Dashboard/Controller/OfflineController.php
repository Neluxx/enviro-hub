<?php

declare(strict_types=1);

namespace App\Dashboard\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Offline Controller.
 */
class OfflineController extends AbstractController
{
    #[Route('/offline')]
    public function offline(): Response
    {
        return $this->render('@Dashboard/offline.html.twig');
    }
}
