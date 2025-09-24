<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MessagerieController extends AbstractController
{
    #[Route('/messagerie', name: 'app_messagerie')]
    public function index(): Response
    {
        return $this->render('messagerie/messagerie.html.twig', [
            'controller_name' => 'MessagerieController',
        ]);
    }
}
