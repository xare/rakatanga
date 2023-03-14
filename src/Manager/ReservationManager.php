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

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected EventDispatcherInterface $eventDispatcher,
        protected logHelper $logHelper,
        protected reservationHelper $reservationHelper
  ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->logHelper = $logHelper;
        $this->reservationHelper = $reservationHelper;
    }

     /**
      * @return Reservation $reservation
      */
     public function createReservation(
                        array $requestData,
                        Dates $date,
                        User $user,
                        string $locale)
     {
         $reservation = $this->reservationHelper->makeReservation($requestData, $date, $user, $locale);
         $this->logHelper->logReservationInitialize($reservation->getDate(), $reservation);

         return $reservation;
     }

     public function sendReservation(
                        Reservation $reservation
     ) {
            $event = new ReservationEvent($reservation);
            try  {
                $this->eventDispatcher->dispatch($event);
            } catch (\Exception $exception) {
                dump($exception);
            }
     }
}
