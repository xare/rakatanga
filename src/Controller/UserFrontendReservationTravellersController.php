<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Travellers;
use App\Form\ReservationDataType;
use App\Repository\DocumentRepository;
use App\Repository\LangRepository;
use App\Repository\ReservationDataRepository;
use App\Repository\TravellersRepository;
use App\Service\breadcrumbsHelper;
use App\Service\languageMenuHelper;
use App\Service\Mailer;
use App\Service\reservationDataHelper;
use App\Service\reservationHelper;
use App\Service\travellersHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class UserFrontendReservationTravellersController extends AbstractController
{

    public function __construct(
            private EntityManagerInterface $entityManager,
            private TranslatorInterface $translatorInterface,
            private Breadcrumbs $breadcrumbs,
            private Mailer $mailer,
            private breadcrumbsHelper $breadcrumbsHelper,
            private languageMenuHelper $languageMenuHelper,
            private TravellersRepository $travellersRepository,
            private LangRepository $langRepository,
            private DocumentRepository $documentRepository,
            private ReservationDataRepository $reservationDataRepository,
            private ReservationHelper $reservationHelper,
            private travellersHelper $travellersHelper,
            private reservationDataHelper $reservationDataHelper)
    {

    }

   #[Route(
        path: ['/user/reservation/data/{reservation}/{traveller}'],
        name: 'frontend_user_reservation_data_traveller',
        requirements: ['reservation' => '\d+', 'traveller' => '\d+'])]
    #[Route(
        path: [
            'en' => '{_locale}/user/reservation/data/{reservation}/{traveller}',
            'es' => '{_locale}/usuario/reserva/datos/{reservation}/{traveller}',
            'fr' => '{_locale}/utilisateur/reservation/donnees/{reservation}/{traveller}'],
        name: 'frontend_user_reservation_data_traveller',
        requirements: ['reservation' => '\d+', 'traveller' => '\d+'])]
    public function frontendUserReservationDataTraveller(
        Request $request,
        Reservation $reservation,
        Travellers $traveller,
        string $_locale = null,
        string $locale = 'es'
    ) {
        $locale = $_locale ?: $locale;

        $urlArray = $this->languageMenuHelper->basicLanguageMenu($locale);

        $reservationData = $this->reservationDataRepository->findOneBy([
            'reservation' => $reservation,
            'traveller' => $traveller,
        ]);

        $form = $this->createForm(ReservationDataType::class, $reservationData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reservationData = $form->getData();
            $reservationData->setReservation($reservation);
            $reservationData->setTraveller($traveller);
            $this->entityManager->persist($reservationData);
            $this->entityManager->flush();
            $this->mailer->sendEmailonDataCompletionToUs($reservation);
        }

        $documents = $this->documentRepository->findBy([
            'reservation' => $reservation,
            'traveller' => $traveller,
        ]);

        $array = [];
        $i = 0;
        foreach ($reservation->getReservationData() as $reservationDatum) {
            $array[$i] = $reservationDatum;
        }

        $fieldsCompletion = $reservationData == null ? ['filledFieldsCount' => 0, 'fieldsCount'=>1] :$this->reservationDataHelper->getReservationDataFields($reservationData);
        $otherTravellers = $this->travellersRepository->listOtherTravellers($traveller, $reservation);
        return $this->render('user/user_reservation_data.html.twig', [
            'langs' => $urlArray,
            'locale' => $locale,
            'traveller' => $traveller,
            'reservation' => $reservation,
            'reservationData' => $reservationData,
            'fieldsCompletion' => $fieldsCompletion,
            'documents' => $documents,
            'otherTravellers' => $otherTravellers,
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/user/reservation/data/{reservation}/travellers/save/',
        name: 'user_reservation_travellers_save',
        requirements: ['reservation' => '\d+'],
        methods: ['POST'])]
    #[Route(
        path: [
            'en' => '{_locale}/user/reservation/data/{reservation}/travellers/save/',
            'es' => '{_locale}/usuario/reserva/datos/{reservation}/travellers/save/',
            'fr' => '{_locale}/utilisateur/reservation/donnees/{reservation}/travellers/save/'],
        name: 'user_reservation_travellers_save',
        requirements: ['reservation' => '\d+'],
        methods: ['POST'])]
    public function userReservationTravellersSave(
        Request $request,
        Reservation $reservation,
        LangRepository $langRepository,
    ) {
        $locale = $request->request->get('locale');
        $travellersArray = $request->request->all();
        $reservationData = $this->reservationDataRepository->find($reservation->getId());

        foreach($travellersArray['traveller'] as $travellerElement) {
            $this->travellersHelper->addTravellerToReservation(
                $travellerElement,
                $reservation,
                $this->getUser());
        }
        $this->entityManager->flush();
        $lang = $this->langRepository->findOneBy([
            'iso_code' => $locale,
        ]);
        $urlArray = $this->languageMenuHelper->basicLanguageMenu($locale);


        $options = $this->reservationHelper->getReservationOptions($reservation, $lang);
        $this->addFlash('success',
            "{$this->translatorInterface->trans('Gracias, hemos guardado tus datos correctamente')}
                            {$this->translatorInterface->trans('Puedes volver a tu reserva')}".' <a href='.$this->generateUrl('frontend_user_reservations').">{$this->translatorInterface->trans('aquí')}".'</a>');

        $isTravellerInReservation = false;

        return $this->render('user/user_reservation_travellers.html.twig', [
            'reservation' => $reservation,
            'nbpilotes' => $reservation->getNbpilotes(),
            'nbaccomp' => $reservation->getNbAccomp(),
            'optionsJson' => json_encode($options),
            'date' => $reservation->getDate(),
            'langs' => $urlArray,
        ]);
    }

    #[Route(
        path: '/user/reservation/{reservation}/travellers/',
        name: 'user_reservation_travellers',
        methods: ['GET'])]
    #[Route(
        path: [
            'en' => '{_locale}/user/reservation/data/{reservation}/travellers/',
            'es' => '{_locale}/usuario/reserva/datos/{reservation}/travellers/',
            'fr' => '{_locale}/utilisateur/reservation/donnees/{reservation}/travellers/'],
        name: 'user_reservation_travellers',
        methods: ['GET'])]
    public function userReservationTravellers(
        Reservation $reservation,
        string $locale = 'es',
        string $_locale = null
    ) {
        // Swith Locale Loader
        $locale = $_locale ?: $locale;
        $lang = $this->langRepository->findOneBy([
            'iso_code' => $locale
        ]);
        $urlArray = $this->languageMenuHelper->basicLanguageMenu($locale);

        $this->breadcrumbsHelper->reservationTravellersBreadcrumbs($locale);

        $options = $this->reservationHelper->getReservationOptions($reservation, $lang);

        return $this->render('user/user_reservation_travellers.html.twig', [
            'reservation' => $reservation,
            'nbpilotes' => $reservation->getNbpilotes(),
            'nbaccomp' => $reservation->getNbAccomp(),
            'optionsJson' => json_encode($options),
            'date' => $reservation->getDate(),
            'langs' => $urlArray,
        ]);
    }
}
