<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\ReservationOptions;
use App\Repository\LangRepository;
use App\Repository\OptionsRepository;
use App\Repository\ReservationOptionsRepository;
use App\Service\breadcrumbsHelper;
use App\Service\languageMenuHelper;
use App\Service\localizationHelper;
use App\Service\reservationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class UserFrontendReservationController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ReservationHelper $reservationHelper,
        private TranslatorInterface $translatorInterface,
        private Breadcrumbs $breadcrumbs,
        private languageMenuHelper $languageMenuHelper,
        private breadcrumbsHelper $breadcrumbsHelper,
        private langRepository $langRepository,
        private OptionsRepository $optionsRepository,
        private ReservationOptionsRepository $reservationOptionsRepository
        )
    {

    }

    #[Route(
        path: ['/user/reservation/{reservation}'],
        options: ['expose' => true],
        name: 'frontend_user_reservation')]
    #[Route(
        path: [
            'en' => '{_locale}/user/reservation/{reservation}',
            'es' => '{_locale}/usuario/reserva/{reservation}',
            'fr' => '{_locale}/utilisateur/reservation/{reservation}'],
        options: ['expose' => true],
        name: 'frontend_user_reservation')]
    public function frontend_user_reservation(
        Reservation $reservation,
        string $_locale = null,
        string $locale = 'es'
    ) {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $locale = $_locale ? $_locale : $locale;
        $lang = $this->langRepository->findOneBy([
            'iso_code' => $locale,
        ]);

        $urlArray = $this->languageMenuHelper->reservationPaymentMenuLanguage($locale,$reservation);

        $this->breadcrumbsHelper->frontendUserReservationBreadcrumbs($locale,$reservation);

        $options = $this->reservationHelper->getReservedOptions($reservation, $lang->getIsoCode());

        $selectableOptions = $this->reservationHelper->getReservationOptions($reservation, $lang);

        return $this->render('reservation/index.html.twig', [
            'date' => $reservation->getDate(),
            'langs' => $urlArray,
            'locale' => $locale,
            'reservation' => $reservation,
            'selectableOptions' => $selectableOptions,
            'options' => $options,
            'reservationOptions' => $options,
            'optionsJson' => json_encode($options),
            'isInitialized' => true,
            'userEdit' => true,
        ]);
    }

    #[Route(
        path: '/user/reservation/{reservation}/change/option',
        options: ['expose' => true],
        name: 'user_reservation_change_option',
        methods: ['POST'])]
    public function userReservationChangeOption(
        Request $request,
        Reservation $reservation
    ) {
        $optionData = $request->request->get('optionData');
        $options = $this->optionsRepository->find($optionData['id']);
        $reservationOptions = $this->reservationOptionsRepository->findOneBy([
            'reservation' => $reservation,
            'options' => $optionData['id'],
        ]);
        if (!$reservationOptions) {
            $reservationOptions = new ReservationOptions();
            $reservationOptions->setOption($options);
            $reservationOptions->setReservation($reservation);
        }
        $reservationOptions->setAmmount($optionData['ammount']);
        $this->entityManager->persist($reservationOptions);
        $this->entityManager->flush();

        return new Response('nothing');
    }

    #[Route(
        path: '/user/reservation/{reservation}/change/nb',
        options: ['expose' => true],
        name: 'user_reservation_change_nb',
        methods: ['POST'])]
    public function userReservationChangeNb(
        Request $request,
        Reservation $reservation
    ) {
        $nb = $request->request->get('nb');
        $type = $request->request->get('type');
        if ($type == 'Accomp') {
            $reservation->setNbAccomp($nb);
        }
        if ($type == 'pilotes') {
            $reservation->setNbpilotes($nb);
        }
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return new Response('nb changed');
    }
}
