<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutEventSubscriber implements EventSubscriberInterface
{
    private $urlGenerator;
    private $flashBag;

    public function __construct(FlashBagInterface $flashBag, UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->flashBag = $flashBag;
    }

    public function onLogoutEvent(LogoutEvent $event): void
    {
        $this->flashBag->add(
            'success',
            'Déconnecté(e) avec succès !'
        );
        // $event->getRequest()->getSession()->getFlashBag()->add(
        //     'success',
        //     'Déconnecté avec succès !'
        // );

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_home')));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogoutEvent',
        ];
    }
}
