<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\ReservationData;
use App\Entity\Travellers;
use App\Form\ReservationDataType;
use App\Repository\DocumentRepository;
use App\Repository\LangRepository;
use App\Repository\ReservationDataRepository;
use App\Repository\TravellersRepository;
use App\Service\breadcrumbsHelper;
use App\Service\logHelper;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserFrontendReservationDataController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private Mailer $mailer, private breadcrumbsHelper $breadcrumbsHelper, private TranslatorInterface $translator)
    {
    }

    #[Route(path: ['en' => '{_locale}/user/reservation/data/{reservation}', 'es' => '{_locale}/usuario/reserva/datos/{reservation}', 'fr' => '{_locale}/utilisateur/reservation/donnees/{reservation}'], name: 'frontend_user_reservation_data')]
      public function frontend_user_reservation_data(
      Request $request,
      string $_locale = null,
      Reservation $reservation,
      LangRepository $langRepository,
      ReservationDataRepository $reservationDataRepository,
      DocumentRepository $documentRepository,
      EntityManagerInterface $em,
      Mailer $mailer,
      logHelper $logHelper,
      $locale = 'es'
  ) {
          $locale = $_locale ?: $locale;

          /**
           * @var User $user
           */
          $user = $this->getUser();
          // Swith Locale Loader
          $otherLangsArray = $langRepository->findOthers($locale);
          $i = 0;
          $urlArray = [];
          foreach ($otherLangsArray as $otherLangArray) {
              $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
              $urlArray[$i]['lang_name'] = $otherLangArray->getName();
              $urlArray[$i]['reservation'] = $reservation;
              ++$i;
          }
          // End switch locale loader

          // BREADCRUMBS
          $this->breadcrumbsHelper->reservationTravellersBreadcrumbs('Inicio');
          // END BREADCRUMBS

          $reservationData = $reservationDataRepository->findOneBy([
              'reservation' => $reservation,
          ]);

          $reservationData = $reservationData ?: new ReservationData();
          $documents = $reservationData->getDocuments();
          $form = $this->createForm(ReservationDataType::class, $reservationData);
          $form->handleRequest($request);

          if ($form->isSubmitted() && $form->isValid()) {
              $reservationData = $form->getData();
              $reservationData->setReservation($reservation);
              $reservationData->setUser($this->getUser());
              $em->persist($reservationData);
              $em->flush();
              $logHelper->logThis(
                  'Datos aportados a una reserva',
                  "{$user->getPrenom()} {$user->getNom()}[{$user->getEmail()}] ha depositados datos para una reserva para el viaje
              {$reservation->getDate()->getTravel()->getMainTitle()} en
              {$reservation->getDate()->getDebut()->format('d/m/Y')} with reference
              {$reservation->getId()} ".strtoupper(substr($reservation->getDate()->getTravel()->getMainTitle(), 0, 3)).'',
                  [],
                  'reservation');
              $mailer->sendEmailonDataCompletionToUs($reservation);
              $this->addFlash('success', $this->translator->trans('Gracias, hemos guardado tus datos correctamente'));
          }

          return $this->render('user/user_reservation_data.html.twig', [
              'langs' => $urlArray,
              'locale' => $locale,
              'user' => $this->getUser(),
              'reservationData' => $reservationData,
              'reservation' => $reservation,
              'documents' => $documents,
              'form' => $form->createView(),
          ]);
      }

    #[Route(path: 'user/reservationData/new/{reservation}/{traveller}/', methods: ['GET', 'POST'], name: 'frontend-user-reservation-data-new')]
 public function userReservationDataNew(
  Request $request,
  LangRepository $langRepository,
  DocumentRepository $documentRepository,
  Travellers $traveller,
  Reservation $reservation,
  string $_locale = null,
  $locale = 'es')
 {
     $locale = $_locale ?: $locale;
     $documents = [];
     $user = $this->getUser();

     // Swith Locale Loader
     $otherLangsArray = $langRepository->findOthers($locale);
     $i = 0;
     $urlArray = [];
     foreach ($otherLangsArray as $otherLangArray) {
         $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
         $urlArray[$i]['lang_name'] = $otherLangArray->getName();
         ++$i;
     }
     // End switch locale loader

     $reservationData = new ReservationData();
     $form = $this->createForm(ReservationDataType::class, $reservationData);
     $form->handleRequest($request);
     if ($form->isSubmitted() && $form->isValid()) {
         $reservationData = $form->getData();
         $reservationData->setReservation($reservation);
         $reservationData->setTraveller($traveller);
         $this->entityManager->persist($reservationData);
         $this->entityManager->flush();
         $documents = $documentRepository->findBy([
           'reservationData' => $reservationData,
         ]);
         $this->mailer->sendEmailonDataCompletionToUs($reservation);
     }

     return $this->render('user/user_reservation_data_traveller.html.twig', [
       'langs' => $urlArray,
       'reservationData' => $reservationData,
       'locale' => $locale,
       'traveller' => $traveller,
       'reservation' => $reservation,
       'documents' => $documents,
       'form' => $form->createView(),
]);
 }

    #[Route(path: '/user/reservationData/{reservationData}', name: 'frontend-user-reservation-data')]
     public function userReservationData(
      Request $request,
      ReservationData $reservationData,
      LangRepository $langRepository,
      DocumentRepository $documentRepository,
      TravellersRepository $travellersRepository,
      string $_locale = null,
      $locale = 'es')
     {
         $locale = $_locale ?: $locale;
         $documents = [];
         $reservation = $request->request->get('reservation');
         $travellerId = $request->request->get('traveller');
         if ($travellerId != null) {
             $traveller = $travellersRepository->find($travellerId);
         }
         $user = $this->getUser();

         // Swith Locale Loader
         $otherLangsArray = $langRepository->findOthers($locale);
         $i = 0;
         $urlArray = [];
         foreach ($otherLangsArray as $otherLangArray) {
             $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
             $urlArray[$i]['lang_name'] = $otherLangArray->getName();
             ++$i;
         }
         // End switch locale loader

         $form = $this->createForm(ReservationDataType::class, $reservationData);
         $form->handleRequest($request);
         if ($form->isSubmitted() && $form->isValid()) {
             $reservationData = $form->getData();
             $reservationData->setReservation($reservation);
             $reservationData->setTraveller($traveller);
             $this->entityManager->persist($reservationData);
             $this->entityManager->flush();
             $documents = $documentRepository->findBy([
               'reservationData' => $reservationData,
             ]);
             $this->mailer->sendEmailonDataCompletionToUs($reservation);
         }
         if ($travellerId != null) {
             return $this->render('user/user_reservation_data_traveller.html.twig', [
               'langs' => $urlArray,
               'reservationData' => $reservationData,
               'locale' => $locale,
               'traveller' => $traveller,
               'reservation' => $reservation,
               'documents' => $documents,
               'form' => $form->createView(),
    ]);
         } else {
             return $this->render('user/user_reservation_data.html.twig', [
               'langs' => $urlArray,
               'reservationData' => $reservationData,
               'reservation' => $reservationData->getReservation(),
               'locale' => $locale,
               'reservation' => $reservation,
               'documents' => $documents,
               'form' => $form->createView(),
    ]);
         }
     }
}
