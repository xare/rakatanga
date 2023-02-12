<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\ReservationData;
use App\Entity\User;
use App\Repository\PaymentsRepository;
use App\Repository\ReservationDataRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;

class reservationDataHelper
{
    public function __construct(
        private PaymentsRepository $paymentsRepository,
        private EntityManagerInterface $entityManager,
        private ReservationDataRepository $reservationDataRepository)
    {
    }

      /**
       * getReservationAmmount.
       */
      public function getReservationAmmount(Reservation $reservation): int
      {
          $ammountBeforeDiscount = $this->getReservationAmmountBeforeDiscount($reservation);
          $discount = $this->getDiscount($reservation, $ammountBeforeDiscount);

          return $ammountBeforeDiscount - $discount;
      }

      /**
       * getReservationAmmountBeforeDiscount function.
       */
      public function getReservationAmmountBeforeDiscount(Reservation $reservation): int
      {
          /**
           * @var int $total
           */
          $total = ($reservation->getNbpilotes() * $reservation->getDate()->getPrixPilote())
                  + ($reservation->getNbAccomp() * $reservation->getDate()->getPrixaccomp());

          /**
           * @var ReservationOptions $reservationOption
           */
          foreach ($reservation->getReservationOptions() as $reservationOption) {
              $total += $reservationOption->getAmmount() * $reservationOption->getOption()->getPrice();
          }

          return $total;
      }

    /**
     * getDiscount function.
     */
    public function getDiscount(Reservation $reservation, int $total = 0): int
    {
        /**
         * @var int $discount
         */
        $discount = 0;

        if ($reservation->getCodespromo() !== null) {
            if ($reservation->getCodespromo()->getMontant() > 0) {
                $discount += $reservation->getCodespromo()->getMontant();
            } elseif ($reservation->getCodespromo()->getPourcentage()) {
                $discount += $total * $reservation->getCodespromo()->getPourcentage() / 100;
            }
        }

        return $discount;
    }

    public function getReservationTotalPayments(Reservation $reservation)
    {
        $payments = $this->paymentsRepository->findBy([
            'reservation' => $reservation,
        ]);

        $totalDueAmmount = 0;
        foreach ($payments as $payment) {
            $totalDueAmmount = +$payment->getAmmount();
        }

        return $totalDueAmmount;
    }

    public function getReservationDueAmmount(Reservation $reservation)
    {
        dump($reservation);
        dump($this->getReservationAmmount($reservation));
        $totalAmmount = $this->getReservationAmmount($reservation);
        $totalPayments = $this->getReservationTotalPayments($reservation);

        $dueAmmount = $totalAmmount - $totalPayments;

        return $dueAmmount;
    }

    public function getReservationDataFields(ReservationData $reservationData){
        $reflection = new \ReflectionObject($reservationData);

        $getterMethods = array_filter($reflection->getMethods(\ReflectionMethod::IS_PUBLIC), function (\ReflectionMethod $method) {
            return substr($method->getName(), 0, 3) === 'get';
        });

        $filledFieldsCount = 0;
        $fieldsCount = count($getterMethods);
        foreach ($getterMethods as $method) {
            $propertyName = lcfirst(substr($method->getName(), 3));
            if($propertyName == "reservation" || $propertyName == "documents"  || $propertyName == "user"){
                $fieldsCount --;
                continue;
            }
            /* $property = $reflection->getProperty($propertyName); */
            if ($reservationData->{$method->getName()}() !== null) {
                $filledFieldsCount++;
            }
        }

        $array['filledFieldsCount'] = $filledFieldsCount;
        $array['fieldsCount'] = $fieldsCount;
    return $array;
    }

    public function getUserLatestData(User $user) {
        return $this->reservationDataRepository->getUserLatestData($user);
    }
}
