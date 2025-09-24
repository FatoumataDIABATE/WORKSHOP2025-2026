<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StocksController extends AbstractController
{
    #[Route('/stocks', name: 'app_stocks')]
    public function index(): Response
    {
        return $this->render('stocks/index.html.twig', [
            'controller_name' => 'StocksController',
        ]);
    }
}
