<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Offline Controller.
 */
class OfflineController extends AbstractController
{
    #[Route('/offline', name: 'app_offline')]
    public function offline(): Response
    {
        return $this->render('offline.html.twig');
    }
}
