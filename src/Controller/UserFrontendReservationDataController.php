<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\ReservationData;
use App\Entity\Travellers;
use App\Form\ReservationDataType;
use App\Repository\DocumentRepository;
use App\Repository\LangRepository;
use App\Repository\TravellersRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserFrontendReservationDataController extends AbstractController
{

  private $entityManager;
  private $mailer;

  public function __construct( EntityManagerInterface $entityManager, Mailer $mailer )
  {
    $this->entityManager = $entityManager;
    $this->mailer = $mailer;
  }
  /** 
  * @Route("user/reservationData/new/{reservation}/{traveller}/", 
  * methods={"GET","POST"},
  * name="frontend-user-reservation-data-new")
  */

 public function userReservationDataNew(
  Request $request,
  LangRepository $langRepository,
  DocumentRepository $documentRepository,
  Travellers $traveller,
  Reservation $reservation,
  string $_locale = null,
  $locale = "es")
 {

  $locale = $_locale ?: $locale;
  $documents = [];
  $user = $this->getUser();

  //Swith Locale Loader
  $otherLangsArray = $langRepository->findOthers($locale);
  $i = 0;
  $urlArray = [];
  foreach ($otherLangsArray as $otherLangArray) {
      $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
      $urlArray[$i]['lang_name'] = $otherLangArray->getName();
      $i++;
  }
  //End switch locale loader

  $reservationData = new ReservationData();
  $form = $this->createForm(ReservationDataType::class, $reservationData);
  $form->handleRequest($request);
  if($form->isSubmitted() && $form->isValid())
  {
    $reservationData = $form->getData();
    $reservationData->setReservation($reservation);
    $reservationData->setTraveller($traveller);
    $this->entityManager->persist($reservationData);
    $this->entityManager->flush();
    $documents = $documentRepository->findBy([
      'reservationData' => $reservationData
    ]);
    $this->mailer->sendEmailonDataCompletionToUs($reservation); 
  }

  return $this->render("user/user_reservation_data_traveller.html.twig", [
    'langs' => $urlArray,
    'reservationData' => $reservationData,
    'locale' => $locale,
    'traveller' => $traveller,
    'reservation' => $reservation,
    'documents' => $documents,
    'form' => $form->createView()
]);
 }

  /**
   * @Route("/user/reservationData/{reservationData}", name="frontend-user-reservation-data")
   */

   public function userReservationData(
      Request $request,
      ReservationData $reservationData,
      LangRepository $langRepository,
      DocumentRepository $documentRepository,
      TravellersRepository $travellersRepository,
      string $_locale = null,
      $locale = "es" )
   {
    $locale = $_locale ?: $locale;
    $documents = [];
    $reservation = $request->request->get("reservation");
    $travellerId = $request->request->get("traveller");
    if($travellerId != null)
      $traveller = $travellersRepository->find($travellerId);
    $user = $this->getUser();

    //Swith Locale Loader
    $otherLangsArray = $langRepository->findOthers($locale);
    $i = 0;
    $urlArray = [];
    foreach ($otherLangsArray as $otherLangArray) {
        $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
        $urlArray[$i]['lang_name'] = $otherLangArray->getName();
        $i++;
    }
    //End switch locale loader


    $form = $this->createForm(ReservationDataType::class, $reservationData);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid())
    {
      $reservationData = $form->getData();
      $reservationData->setReservation($reservation);
      $reservationData->setTraveller($traveller);
      $this->entityManager->persist($reservationData);
      $this->entityManager->flush();
      $documents = $documentRepository->findBy([
        'reservationData' => $reservationData
      ]);
      $this->mailer->sendEmailonDataCompletionToUs($reservation); 
    }
    if($travellerId != null) {
      return $this->render("user/user_reservation_data_traveller.html.twig", [
        'langs' => $urlArray,
        'reservationData' => $reservationData,
        'locale' => $locale,
        'traveller' => $traveller,
        'reservation' => $reservation,
        'documents' => $documents,
        'form' => $form->createView()
    ]);
    } else {
      
      return $this->render( "user/user_reservation_data.html.twig", [
        'langs' => $urlArray,
        'reservationData' => $reservationData,
        'reservation' => $reservationData->getReservation(),
        'locale' => $locale,
        'reservation' => $reservation,
        'documents' => $documents,
        'form' => $form->createView()
    ]);
    }
   }
}