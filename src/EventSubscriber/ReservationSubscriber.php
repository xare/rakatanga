<?php

namespace App\EventSubscriber;

use App\Event\ReservationEvent;
use App\Service\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ReservationSubscriber implements EventSubscriberInterface
{
    /**
     * @var Mailer
     */


    public function __construct(
        private Mailer $mailer,
        private RequestStack $requestStack)
    { }

    public function onReservationEvent(ReservationEvent $event)
    {
        // Get the current locale from the request
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request->getLocale() ?: 'es';
        $reservation = $event->getReservation();
        $this->mailer->sendReservationToSender($reservation, $locale);
        $this->mailer->sendReservationToUs($reservation, 'es');
    }

    public function onReservationUpdateEvent(ReservationEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request->getLocale() ?: 'es';
        $reservation = $event->getReservation();
        $this->mailer->sendReservationUpdateToSender($reservation, $locale);
        $this->mailer->sendReservationUpdateToUs($reservation, 'es');

    }

    public static function getSubscribedEvents(): array
    {
        return [
            ReservationEvent::class => 'onReservationEvent',
            ReservationEvent::class => 'onReservationUpdateEvent',
        ];
    }
}
