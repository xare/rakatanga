<?php

namespace App\Manager;

use App\Entity\Dates;
use App\Entity\Reservation;
use App\Entity\User;
use App\Event\ReservationEvent;
use App\Service\logHelper;
use App\Service\reservationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ReservationManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var reservationHelper
     */
    protected $reservationHelper;

    /**
     * @param logHelper $reservationHelper
     */
    public function __construct(
    EntityManagerInterface $entityManager,
    EventDispatcherInterface $eventDispatcher,
    logHelper $logHelper,
    reservationHelper $reservationHelper
  ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->logHelper = $logHelper;
        $this->reservationHelper = $reservationHelper;
    }

     /**
      * @return Reservation $reservation
      */
     public function sendReservation(array $requestData, Dates $date, User $user, string $locale)
     {
         $reservation = $this->reservationHelper->makeReservation($requestData, $date, $user, $locale);
         $this->logHelper->logReservationInitialize($reservation->getDate(), $reservation);

         $event = new ReservationEvent($reservation);
         $this->eventDispatcher->dispatch($event);

         return $reservation;
     }
}
