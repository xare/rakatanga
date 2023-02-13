<?php

namespace App\Service;

use App\Entity\Document;
use App\Entity\Reservation;
use App\Entity\Travellers;
use App\Entity\User;
use App\Repository\DocumentRepository;

class documentHelper {
  public function __construct(private DocumentRepository $documentRepository){

  }

  public function getDocumentsByReservationByUser(Reservation $reservation) {
    return $this->documentRepository->getDocumentsByReservationByUser($reservation);
  }

  public function getDocumentsByReservationByTraveller($reservation, $traveller) {
    return $this->documentRepository->getDocumentsByReservationByTraveller($reservation, $traveller);
  }

  public function hasDoctype(Document $document, string $doctype):mixed {
    if( $this->documentRepository->findBy( [
      'id' => $document->getId(),
      'doctype' => $doctype ] ) != null ) {
        return true;
      }
    return false;
  }

  public function notPresentDoctypeUser(Reservation $reservation, User $user, string $doctype) {
    $documents = $this->documentRepository->getDocumentsByReservationByUser($reservation,$user);

    array_filter($documents,function($document) use($doctype){
      if ($document->getDoctype() === $doctype) return false;
    });
    return true;
  }
  public function notPresentDoctypeTraveller(Reservation $reservation, Travellers $traveller, string $doctype) {
    $documents = $this->documentRepository->getDocumentsByReservationByUser($reservation, $traveller);
    array_filter($documents,function($document) use($doctype){
      if ($document->getDoctype() === $doctype) return false;
    });
    return true;
  }



}