<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Repository\PaymentsRepository;

class reservationDataHelper
{
    public function __construct(private PaymentsRepository $paymentsRepository)
    {
        $this->paymentsRepository = $paymentsRepository;
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
              $total += $reservationOption->getAmmount() * $reservationOption->getOptions()->getPrice();
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
        dump($this->getReservationAmmount($reservation));
        $totalAmmount = $this->getReservationAmmount($reservation);
        $totalPayments = $this->getReservationTotalPayments($reservation);

        $dueAmmount = $totalAmmount - $totalPayments;

        return $dueAmmount;
    }
}
