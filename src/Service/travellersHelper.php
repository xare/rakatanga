<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Travellers;
use App\Repository\TravellersRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;

class travellersHelper {
    public function __construct(
      private EntityManagerInterface $entityManager,
      private Travellers $traveller,
      private TravellersRepository $travellersRepository
      ) {
        
    }

    public function addTravellerToReservation(Travellers $traveller, Reservation $reservation):bool {
      return true;
    }

    public function removeTravellerFromReservation(Travellers $traveller, Reservation $reservation):bool {
      return true;
    }

    public function updateTravellerData(Travellers $traveller, array $travellerData):bool {
      return true;
    }

}