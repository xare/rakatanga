<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Event\ReservationEvent;
use App\Service\Mailer;

class ReservationSubscriber implements EventSubscriberInterface
{
    /**
   * @var Mailer $mailer
   */
    private $mailer;

    public function __construct(Mailer $mailer){
        $this->mailer = $mailer;
    }
    public function onReservationEvent(ReservationEvent $event)
    {
        $reservation = $event->getReservation();
        $this->mailer->sendReservationToSender($reservation);
        $this->mailer->sendReservationToUs($reservation,'es');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ReservationEvent::class => 'onReservationEvent',
        ];
    }
}
