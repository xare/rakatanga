<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\ReservationData;
use App\Entity\Travellers;
use App\Form\ReservationDataType;
use App\Repository\DocumentRepository;
use App\Repository\LangRepository;
use App\Repository\ReservationDataRepository;
use App\Repository\ReservationRepository;
use App\Repository\TravellersRepository;
use App\Service\breadcrumbsHelper;
use App\Service\languageMenuHelper;
use App\Service\logHelper;
use App\Service\Mailer;
use App\Service\reservationDataHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class UserFrontendReservationDataController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Mailer $mailer,
        private breadcrumbsHelper $breadcrumbsHelper,
        private TranslatorInterface $translator,
        private languageMenuHelper $languageMenuHelper,
        private ReservationRepository $reservationRepository,
        private ReservationDataRepository $reservationDataRepository,
        private reservationDataHelper $reservationDataHelper)
    {
    }

    #[Route(
        path: [
            'en' => '{_locale}/user/reservation/data/{reservation}',
            'es' => '{_locale}/usuario/reserva/datos/{reservation}',
            'fr' => '{_locale}/utilisateur/reservation/donnees/{reservation}'],
        name: 'frontend_user_reservation_data')]
      public function frontend_user_reservation_data(
                            Request $request,
                            string $_locale = null,
                            Reservation $reservation,
                            ReservationDataRepository $reservationDataRepository,
                            logHelper $logHelper,
                            string $locale = 'es'
        ) {
          $locale = $_locale ?: $locale;

          /**
           * @var User $user
           */
          $user = $this->getUser();
          // Swith Locale Loader

          $urlArray = $this->languageMenuHelper->basicLanguageMenu($locale, $reservation);
          $this->breadcrumbsHelper->reservationTravellersBreadcrumbs('Inicio');

          $reservationData = new ReservationData();
          if( $this->reservationDataRepository->findOneBy([
              'reservation' => $reservation,
          ]) != null){
            $reservationData = $this->reservationDataRepository->findOneBy([
                'reservation' => $reservation,
            ]);
        } elseif ($this->reservationDataRepository->getUserLatestData($user)!= null) {
            $previousReservationData = $this->reservationDataRepository->getUserLatestData($user);
            $reservationData->setPassportNo($previousReservationData->getPassportNo());
            $reservationData->setPassportIssueDate($previousReservationData->getPassportIssueDate());
            $reservationData->setPassportExpirationDate($previousReservationData->getPassportExpirationDate());
            $reservationData->setVisaNumber($previousReservationData->getVisaNumber());
            $reservationData->setVisaIssueDate($previousReservationData->getVisaIssueDate());
            $reservationData->setVisaExpirationDate($previousReservationData->getVisaExpirationDate());
            $reservationData->setDriversNumber($previousReservationData->getDriversNumber());
            $reservationData->setDriversIssueDate($previousReservationData->getDriversIssueDate());
            $reservationData->setDriversExpirationDate($previousReservationData->getDriversExpirationDate());
        }
          $fieldsCompletion = $this->reservationDataHelper->getReservationDataFields($reservationData);
          $documents = $reservationData->getDocuments();
          dump($documents);
          $form = $this->createForm(ReservationDataType::class, $reservationData);
          $form->handleRequest($request);

          if ($form->isSubmitted() && $form->isValid()) {
              $reservationData = $form->getData();
              $reservationData->setReservation($reservation);
              $reservationData->setUser($this->getUser());
              $this->entityManager->persist($reservationData);
              $this->entityManager->flush();
              $logHelper->logThis(
                  'Datos aportados a una reserva',
                  "{$user->getPrenom()} {$user->getNom()}[{$user->getEmail()}] ha depositados datos para una reserva para el viaje
              {$reservation->getDate()->getTravel()->getMainTitle()} en
              {$reservation->getDate()->getDebut()->format('d/m/Y')} with reference
              {$reservation->getId()} ".strtoupper(substr($reservation->getDate()->getTravel()->getMainTitle(), 0, 3)).'',
                  [],
                  'reservation');
              $this->mailer->sendEmailonDataCompletionToUs($reservation);
              $this->addFlash('success', $this->translator->trans('Gracias, hemos guardado tus datos correctamente'));
          }

          return $this->render('user/user_reservation_data.html.twig', [
              'langs' => $urlArray,
              'locale' => $locale,
              'user' => $this->getUser(),
              'reservationData' => $reservationData,
              'fieldsCompletion' => $fieldsCompletion,
              'reservation' => $reservation,
              'documents' => $documents,
              'form' => $form->createView(),
          ]);
      }

    #[Route(
        path: 'user/reservationData/new/{reservation}/{traveller}/',
        methods: ['GET', 'POST'],
        name: 'frontend-user-reservation-data-new')]
 public function userReservationDataNew(
  Request $request,
  DocumentRepository $documentRepository,
  Travellers $traveller,
  Reservation $reservation,
  string $_locale = null,
  string $locale = 'es')
 {
     $locale = $_locale ?: $locale;
     $documents = [];
     $user = $this->getUser();

     $urlArray = $this->languageMenuHelper->basicLanguageMenu($locale);
     dump($this->reservationDataRepository->getUserLatestData($user));

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

    #[Route(
        path: '/user/reservationData/{reservationData}',
        name: 'frontend-user-reservation-data')]
     public function userReservationData(
      Request $request,
      ReservationData $reservationData,
      DocumentRepository $documentRepository,
      TravellersRepository $travellersRepository,
      string $_locale = null,
      string $locale = 'es')
     {
         $locale = $_locale ?: $locale;
         $documents = [];
         $reservation = $request->request->get('reservation');
         $travellerId = $request->request->get('traveller');
         if ($travellerId != null) {
             $traveller = $travellersRepository->find($travellerId);
         }

         $urlArray = $this->languageMenuHelper->basicLanguageMenu($locale);

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

     #[Route(
        path: "check/reservation/data",
        methods: ["GET"],
        name: "check-reservation-data",
        options: ['expose' => true]
     )]
     public function checkReservationData():Response {
        $reservation = $this->reservationRepository->getLatestReservation();
        $reservationData = $this->reservationDataRepository->getUserLatestData($this->getUser());
        dump($reservationData);
        if($reservationData instanceOf ReservationData){
            $reservationDataFieldsArray = $this->reservationDataHelper->getReservationDataFields($reservationData);
            if ( $reservationDataFieldsArray['fieldsCount'] != $reservationDataFieldsArray['filledFieldsCount']) {
                $ratio = ($reservationDataFieldsArray['filledFieldsCount']*100/$reservationDataFieldsArray['fieldsCount']);

                $html = $this->renderView('user/partials/_swal_missing_reservationData.html.twig');
                return $this->json([
                    'ratio' => $ratio,
                    'title' => "Faltan datos y documentos",
                    'message' => $html
                ], 200, [], []);
            }
        }
        return $this->json([
                'message' => "Faltan datos y documentos"
        ], 200, [], []);
    }
}
