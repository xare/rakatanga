<?php

namespace App\Event;

use App\Entity\Reservation;
use Symfony\Contracts\EventDispatcher\Event;

class ReservationEvent extends Event
{
  const TEMPLATE_CONTACT_SENDER = "email/reservation/reservation-sender.html.twig";
  const TEMPLATE_CONTACT_RAKATANGA = "email/contact/reservation-rakatanga.html.twig";

  private $reservation;
  /**
   * @param Reservation $reservation
   */
  public function __construct(Reservation $reservation){
    $this->reservation = $reservation;
  }
  
  /**
   * @return Reservation
   */

   public function getReservation(): Reservation
   {
    return $this->reservation;
   }
}