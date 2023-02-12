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
use App\Service\languageMenuHelper;
use App\Service\logHelper;
use App\Service\Mailer;
use App\Service\reservationDataHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

          if( $this->reservationDataRepository->findOneBy([
              'reservation' => $reservation,
          ]) != null){
            $reservationData = $this->reservationDataRepository->findOneBy([
                'reservation' => $reservation,
            ]);
        } elseif ($this->reservationDataRepository->getUserLatestData($user)!= null) {
            $reservationData = $this->reservationDataRepository->getUserLatestData($user);
            $this->addFlash('success',$this->translator->trans('Hemos recuperado algunos datos de tu última reserva y tienes, la parte de la documentación personal, ya rellenada. Pero si algo ha cambiado puedes cambiarlo aquí.'));
        } else {
            $reservationData = new ReservationData();
        }
          $this->reservationDataRepository->getUserLatestData($user);
          $fieldsCompletion = $this->reservationDataHelper->getReservationDataFields($reservationData);
          $reservationData = $reservationData ?: new ReservationData();
          $documents = $reservationData->getDocuments();
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
}
