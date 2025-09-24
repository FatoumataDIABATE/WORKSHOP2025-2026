<?php


namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Message;
use App\Form\MessageFormType;
use Doctrine\ORM\EntityManagerInterface;

class MessagerieController extends AbstractController
{
    #[Route('/messagerie', name: 'app_messagerie')]
    public function index(UserRepository $userRepository): Response
    {
        // Récupère l'utilisateur connecté
        $currentUser = $this->getUser();

        // Récupérer tous les utilisateurs sauf l'utilisateur connecté
        $users = $userRepository->createQueryBuilder('u')
            ->where('u != :currentUser')
            ->setParameter('currentUser', $currentUser)
            ->getQuery()
            ->getResult();

        return $this->render('messagerie/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/messagerie/chat/{id}', name: 'app_messagerie_chat')]
    public function chat(User $user, Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $currentUser = $this->getUser();

        // Création du formulaire
        $message = new Message();
        $form = $this->createForm(MessageFormType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setSender($currentUser);
            $message->setReceiver($user);
            $message->setCreatedAt(new \DateTimeImmutable());;

            $em->persist($message);
            $em->flush();

            return $this->redirectToRoute('app_messagerie_chat', ['id' => $user->getId()]);
        }

        // Récupérer tous les messages entre les deux utilisateurs
        $messages = $em->getRepository(Message::class)->createQueryBuilder('m')
            ->where('(m.sender = :current AND m.receiver = :user) OR (m.sender = :user AND m.receiver = :current)')
            ->setParameter('current', $currentUser)
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('messagerie/chat.html.twig', [
            'chatUser' => $user,
            'messages' => $messages,
            'form' => $form->createView(),
        ]);
    }
}

