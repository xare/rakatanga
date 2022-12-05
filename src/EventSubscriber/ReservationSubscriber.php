<?php

namespace App\EventSubscriber;

use App\Event\ReservationEvent;
use App\Service\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReservationSubscriber implements EventSubscriberInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function onReservationEvent(ReservationEvent $event)
    {
        $reservation = $event->getReservation();
        $this->mailer->sendReservationToSender($reservation);
        $this->mailer->sendReservationToUs($reservation, 'es');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ReservationEvent::class => 'onReservationEvent',
        ];
    }
}
