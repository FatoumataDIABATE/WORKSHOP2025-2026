<?php
namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LastLoginSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents(): array
    {
       
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        
        if (!is_object($user) || !method_exists($user, 'setLastLogin')) {
            return;
        }

        $user->setLastLogin(new \DateTimeImmutable());

        
        $this->em->flush();
    }
}
