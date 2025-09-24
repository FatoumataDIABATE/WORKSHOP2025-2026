<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $registerError = null;
        $registerSuccess = null;

        if ($request->isMethod('POST')) {
            $pseudo = $request->request->get('pseudo');
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            if ($password !== $passwordConfirm) {
                $registerError = "Les mots de passe ne correspondent pas.";
            } elseif (empty($pseudo) || empty($password)) {
                $registerError = "Veuillez remplir tous les champs.";
            } else {
                // Crée un nouvel utilisateur
                $user = new User();
                $user->setPseudo($pseudo);
                $user->setPassword($passwordHasher->hashPassword($user, $password));

                $em->persist($user);
                $em->flush();

                $registerSuccess = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
            }
        }

        return $this->render('security/login.html.twig', [
            'error' => null,
            'registerError' => $registerError,
            'registerSuccess' => $registerSuccess,
        ]);
    }
}
