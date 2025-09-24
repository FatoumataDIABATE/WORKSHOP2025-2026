<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère l'erreur de connexion (si il y en a)
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier identifiant saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        // Vérifie si on arrive après un logout pour afficher un message
        $logoutSuccess = $request->query->get('logout') ? 'Vous avez été déconnecté avec succès.' : null;

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'logoutSuccess' => $logoutSuccess,
            // Les messages d'inscription seront gérés par le RegistrationController
            'registerError' => null,
            'registerSuccess' => null,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode ne sera jamais exécutée, Symfony gère la déconnexion
        throw new \LogicException('Cette méthode est interceptée par Symfony lors du logout.');
    }
}
