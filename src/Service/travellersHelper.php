<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Travellers;
use App\Entity\User;
use App\Repository\TravellersRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Doctrine\DBAL\Exception;

class travellersHelper {
    public function __construct(
      private EntityManagerInterface $entityManager,
      private TravellersRepository $travellersRepository
      ) {

    }

    /* Fill the below function with code that would store a travellers object (as defined here in the entity folder), to a reservation */
    public function addTravellerToReservation(
      array $travellerData,
      Reservation $reservation,
      User $user):mixed {
        /* if(
          $travellerData['email'] == $user->getEmail()
          &&
          $this->travellersRepository->findBy(['email' =>$travellerData['email']]) != null
        ) {
          return false;
        } */
        /* if ($this->travellersRepository->find($travellerData['id']) != null ){
          $traveller =  $this->travellersRepository->find($travellerData['id']);
        } else { */
            $traveller = new Travellers();
        /* } */
        try {
          $traveller->setPrenom($travellerData['prenom']);
          $traveller->setNom($travellerData['nom']);
          $traveller->setEmail($travellerData['email']);
          if($user->getEmail() == $travellerData['email']){
            $traveller->setIsReservationUser(true);
          }
          $traveller->setTelephone($travellerData['telephone']);
          $traveller->setPosition($travellerData['position']);
          $traveller->setUser($user);
          $traveller->setReservation($reservation);
          $this->entityManager->persist($traveller);
          $this->entityManager->flush();
          return true;
        } catch(Exception $error) {
          return $error->getMessage();
        }
    }

    public function removeTravellerFromReservation(
      Travellers $traveller,
      Reservation $reservation):bool {
      return true;
    }

    public function updateTravellerData(
      Travellers $traveller,
      array $travellerData):mixed {
        try {
          $traveller->setPrenom($travellerData['prenom']);
          $traveller->setNom($travellerData['nom']);
          $traveller->setEmail($travellerData['email']);
          $traveller->setTelephone($travellerData['telephone']);
          $this->entityManager->persist($traveller);
          $this->entityManager->flush();
        return true;
      }  catch(Exception $error) {
        return $error->getMessage();
      }
    }

}