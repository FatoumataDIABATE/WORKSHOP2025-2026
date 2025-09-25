<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Message;
use App\Form\MessageFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class MessagerieController extends AbstractController
{
    #[Route('/messagerie/{id?}', name: 'app_messagerie')]
    public function index(?User $user, UserRepository $userRepository, Request $request, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        // Tous les utilisateurs sauf l'utilisateur connecté
        $users = $userRepository->createQueryBuilder('u')
            ->where('u != :currentUser')
            ->setParameter('currentUser', $currentUser)
            ->getQuery()
            ->getResult();

        $chatUser = $user;

        // Si aucun contact n'est sélectionné, prendre le premier avec lequel il y a eu échange
        if (!$chatUser && $users) {
            // Chercher le dernier message reçu ou envoyé
            $lastMessage = $em->getRepository(Message::class)
                ->createQueryBuilder('m')
                ->where('m.sender = :current OR m.receiver = :current')
                ->setParameter('current', $currentUser)
                ->orderBy('m.createdAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($lastMessage) {
                $chatUser = $lastMessage->getSender() === $currentUser
                    ? $lastMessage->getReceiver()
                    : $lastMessage->getSender();
            } else {
                $chatUser = $users[0];
            }

        }

        $messages = [];
        $form = null;

        if ($chatUser) {
            // Récupérer tous les messages entre les deux utilisateurs
            $messages = $em->getRepository(Message::class)
                ->createQueryBuilder('m')
                ->where('(m.sender = :current AND m.receiver = :user) OR (m.sender = :user AND m.receiver = :current)')
                ->setParameter('current', $currentUser)
                ->setParameter('user', $chatUser)
                ->orderBy('m.createdAt', 'ASC')
                ->getQuery()
                ->getResult();

            // Formulaire
            $messageEntity = new Message();
            $form = $this->createForm(MessageFormType::class, $messageEntity);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $messageEntity->setSender($currentUser);
                $messageEntity->setReceiver($chatUser);
                $messageEntity->setCreatedAt(new \DateTimeImmutable());

                $em->persist($messageEntity);
                $em->flush();

                return $this->redirectToRoute('app_messagerie', ['id' => $chatUser->getId()]);
            }
        }

        return $this->render('messagerie/index.html.twig', [
            'users' => $users,
            'chatUser' => $chatUser,
            'messages' => $messages,
            'form' => $form ? $form->createView() : null,
        ]);
    }

    #[Route('/messagerie/ajax/send/{id}', name: 'app_messagerie_ajax_send', methods: ['POST'])]
    public function ajaxSend(User $user, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
    
        $json = json_decode($request->getContent(), true);
        $content =
            ($json['content'] ?? null)                                    
            ?? ($request->request->all('message')['content'] ?? null)      
            ?? $request->request->get('content');                          
    
        if (!$content || trim($content) === '') {
            return $this->json(['success' => false, 'error' => 'Message vide'], 400);
        }
    
        /** @var User $currentUser */
        $currentUser = $this->getUser();
    
        $message = (new Message())
            ->setSender($currentUser)
            ->setReceiver($user)
            ->setContent($content)
            ->setCreatedAt(new \DateTimeImmutable());
    
        $em->persist($message);
        $em->flush();
    
        return $this->json([
            'success' => true,
            'message' => [
                'id'         => $message->getId(),
                'content'    => $message->getContent(),
                'createdAt'  => $message->getCreatedAt()->format('H:i'),
                'senderId'   => $message->getSender()->getId(),
                'receiverId' => $message->getReceiver()->getId(),
            ],
        ]);
    }
    
    




    #[Route('/messagerie/{id}/sync', name: 'app_messagerie_sync', methods: ['GET'])]
    public function sync(User $user, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $currentUser = $this->getUser();
        $afterId = (int) $request->query->get('afterId', 0);

        $qb = $em->getRepository(Message::class)->createQueryBuilder('m')
            ->where('(m.sender = :me AND m.receiver = :other) OR (m.sender = :other AND m.receiver = :me)')
            ->setParameter('me', $currentUser)
            ->setParameter('other', $user)
            ->orderBy('m.id', 'ASC');

        if ($afterId > 0) {
            $qb->andWhere('m.id > :afterId')->setParameter('afterId', $afterId);
        }

        $msgs = $qb->getQuery()->getResult();

        $payload = array_map(function (Message $m) {
            return [
                'id'        => $m->getId(),
                'content'   => $m->getContent(),
                'createdAt' => $m->getCreatedAt()->format('H:i'),
                'senderId'  => $m->getSender()->getId(),
                'receiverId'=> $m->getReceiver()->getId(),
            ];
        }, $msgs);

        return $this->json(['messages' => $payload]);
    }


}
