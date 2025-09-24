<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $searchResult = null;
        $errorMessage = null;

        $pseudo = $request->query->get('pseudo'); // récupération du pseudo depuis la barre de recherche

        if ($pseudo) {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['pseudo' => $pseudo]);

            if ($user) {
                $searchResult = $user;
            } else {
                $errorMessage = "Utilisateur introuvable.";
            }
        }

        return $this->render('home/index.html.twig', [
            'searchResult' => $searchResult,
            'errorMessage' => $errorMessage,
        ]);
    }
}
