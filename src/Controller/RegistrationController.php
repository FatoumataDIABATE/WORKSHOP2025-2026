<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $pseudo = $request->request->get('pseudo');
        $password = $request->request->get('password');
        $passwordConfirm = $request->request->get('password_confirm');

        // Vérifier si le pseudo existe déjà
        if ($userRepository->findOneBy(['pseudo' => $pseudo])) {
            return $this->render('security/login.html.twig', [
                'registerError' => "Ce pseudo existe déjà",
                'activeTab' => 'signup',
            ]);
        }

        // Vérifier la confirmation du mot de passe
        if ($password !== $passwordConfirm) {
            return $this->render('security/login.html.twig', [
                'registerError' => "Les mots de passe ne correspondent pas",
                'activeTab' => 'signup',
            ]);
        }

        // Vérifier la robustesse du mot de passe
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/';
        if (!preg_match($pattern, $password)) {
            return $this->render('security/login.html.twig', [
                'registerError' => "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.",
                'activeTab' => 'signup',
            ]);
        }

        // Créer le nouvel utilisateur
        $user = new User();
        $user->setPseudo($pseudo);
        $user->setPassword(
            $passwordHasher->hashPassword($user, $password)
        );

        $em->persist($user);
        $em->flush();

        return $this->render('security/login.html.twig', [
            'registerSuccess' => "Votre compte a bien été créé ! Vous pouvez vous connecter.",
            'activeTab' => 'signup',
        ]);
    }
}
