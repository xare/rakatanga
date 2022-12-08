<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Reservation;
use App\Entity\ReservationOptions;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\DatesRepository;
use App\Repository\LangRepository;
use App\Repository\OptionsRepository;
use App\Repository\ReservationOptionsRepository;
use App\Repository\ReservationRepository;
use App\Service\breadcrumbsHelper;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class UserFrontendController extends AbstractController
{
    public function __construct(
                        private TranslatorInterface $translatorInterface,
                        private EntityManagerInterface $entityManager,
                        private breadcrumbsHelper $breadcrumbsHelper)
    {
        $this->translator = $translatorInterface;
        $this->entityManager = $entityManager;
        $this->breadcrumbsHelper = $breadcrumbsHelper;
    }

    #[Route(path: '/user', name: 'frontend_user')]
    #[Route(path: ['en' => '{_locale}/user', 'es' => '{_locale}/usuario', 'fr' => '{_locale}/utilisateur'], name: 'frontend_user', priority: 2)]
    public function frontend_user(
        DatesRepository $datesRepository,
        LangRepository $langRepository,
        string $_locale = null,
        string $locale = 'es'
    ) {
        $locale = $_locale ? $_locale : $locale;
        /**
         * @var User $user
         */
        $user = $this->getUser();

        // Swith Locale Loader
        $otherLangsArray = $langRepository->findOthers($locale);
        $urlArray = [];
        $i = 0;
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }

        $dates = $datesRepository->listNextDates();
        $reservations = $user->getReservations();

        return $this->render('user/index.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'dates' => $dates,
            'reservations' => $reservations,
            'user' => $this->getUser(),
        ]);
    }

    #[Route(path: '/user/reservations/', name: 'frontend_user_reservations')]
    #[Route(path: ['en' => '{_locale}/user/reservations/', 'es' => '{_locale}/usuario/reservas/', 'fr' => '{_locale}/utilisateur/reservations/'], name: 'frontend_user_reservations', priority: 2)]
    public function frontend_user_reservations(
        LangRepository $langRepository,
        ReservationRepository $reservationRepository,
        $_locale = null,
        $locale = 'es',
    ) {
        $locale = $_locale ? $_locale : $locale;
        $user = $this->getUser();

        // Swith Locale Loader
        $otherLangsArray = $langRepository->findOthers($locale);
        $urlArray = [];
        $i = 0;
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }
        // End switch Locale loader

        // BREADCRUMBS
        $this->breadcrumbsHelper->userFrontendReservationsBreadcrumbs($locale);

        // END BREADCRUMBS

        $reservations = $reservationRepository->findBy(['user' => $user], ['date_ajout' => 'DESC']);

        return $this->render('/user/user_reservations.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'reservations' => $reservations,
        ]);
    }

    #[Route(path: ['en' => '{_locale}/user/settings', 'es' => '{_locale}/usuario/datos', 'fr' => '{_locale}/utilisateur/donnees'], name: 'frontend_user_settings')]
    public function frontend_user_settings(
        Request $request,
        LangRepository $langRepository,
        Breadcrumbs $breadcrumbs,
        string $_locale = null,
        $locale = 'es'
    ) {
        $locale = $_locale ?: $locale;

        // Swith Locale Loader
        $otherLangsArray = $langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }

        // End swith locale Loader

        $this->breadcrumbsHelper->frontendUserSettingsBreadcrumbs($locale);

        // Create User Form
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        // dd($form);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            // dd($user);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('Gracias, hemos guardado tus datos correctamente'));

            // return $this->redirectToRoute('user_index');
        }

        return $this->render('user/user_settings.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/user/download/document/{document}', name: 'user-download-document2')]
    public function downloadDocument2(
        Document $document,
        UploadHelper $uploadHelper
    ) {
        $response = new StreamedResponse(function () use ($document, $uploadHelper) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $uploadHelper->readStream($document->getFilePath(), false);
            stream_copy_to_stream($fileStream, $outputStream);
        });
        $response->headers->set('Content-Type', 'application/pdf');
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'FACTURA-'.$invoice->getInvoiceNumber().'.pdf'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route(path: '/user/ajax/reservation-manager/changeNb/', name: 'user-ajax-reservation-manager-add-pilote')]
    public function reservationManagerChangeNb(
        Request $request,
        ReservationRepository $reservationRepository
    ) {
        $reservationId = $request->request->get('reservationId');
        $operation = $request->request->get('operation');
        $operationArray = explode('-', $operation);
        $reservation = $reservationRepository->find($reservationId);
        $nb = 0;

        if ($operationArray[1] == 'pilote') {
            $nb = $reservation->getNbpilotes();
            if ($operationArray[0] == 'remove') {
                --$nb;
            }
            if ($operationArray[0] == 'add') {
                ++$nb;
            }
            $reservation->setNbpilotes($nb);
        }
        if ($operationArray[1] == 'passager') {
            $nb = $reservation->getNbAccomp();
            if ($operationArray[0] == 'remove') {
                --$nb;
            }
            if ($operationArray[0] == 'add') {
                ++$nb;
            }
            $reservation->setNbAccomp($nb);
        }

        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $this->json([
            'nb' => $nb,
        ], 200);
    }

    #[Route(path: '/user/ajax/reservation-manager/changeOptionAmmount/', name: 'user-ajax-reservation-manager-change-option-ammount')]
    public function reservationManagerChangeOptionAmmount(
        Request $request,
        ReservationRepository $reservationRepository,
        OptionsRepository $optionsRepository,
        ReservationOptionsRepository $reservationOptionsRepository
    ) {
        $reservationId = $request->request->get('reservationId');
        $optionId = $request->request->get('optionId');
        $operation = $request->request->get('operation');
        $operationArray = explode('-', $operation);
        $reservation = $reservationRepository->find($reservationId);
        $option = $optionsRepository->find($optionId);
        $reservationOption = $reservationOptionsRepository->findOneBy([
            'reservation' => $reservation,
            'options' => $option,
        ]);
        $ammount = $request->request->get('ammount');

        if ($operationArray[0] == 'remove') {
            --$ammount;
        }
        if ($operationArray[0] == 'add') {
            ++$ammount;
        }
        $reservationOption->setAmmount($ammount);

        $this->entityManager->persist($reservationOption);
        $this->entityManager->flush();

        return $this->json([
            'ammount' => $ammount,
        ], 200);
    }

    #[Route(path: '/user/ajax/reservation-manager/updateReservation/{reservation}', name: 'user-ajax-reservation-manager-update-reservation')]
    public function reservationManagerUpdateReservation(
        Request $request,
        Reservation $reservation,
        OptionsRepository $optionsRepository,
        ReservationOptionsRepository $reservationOptionsRepository
    ) {
        $pilotes = $request->request->get('pilotes');
        $Accomp = $request->request->get('Accomp');
        $reservation->setNbpilotes($pilotes);
        $reservation->setNbAccomp($Accomp);

        $options = $request->request->get('options');
        if (count($options) > 0) {
            foreach ($options as $optionItem) {
                $optionId = $optionItem['option']['id'];
                $nb = $optionItem['option']['nb'];
                $option = $optionsRepository->find($optionId);
                $reservationOptions = $reservationOptionsRepository->findOneBy([
                    'reservation' => $reservation,
                    'options' => $option,
                ]);
                if (null !== $reservationOptions) {
                    $reservationOptions->setAmmount($nb);
                    $this->entityManager->persist($reservationOptions);
                } else {
                    $reservationOptions = new ReservationOptions();
                    $reservationOptions->setOptions($option);
                    $reservationOptions->setAmmount($nb);
                    $reservation->addReservationOption($reservationOptions);
                    $this->entityManager->persist($reservation);
                }
            }
        }
        $this->entityManager->flush();

        return $this->redirectToRoute(
            'frontend_user_reservation',
            [
                '_locale' => 'es',
                'reservation' => $reservation->getId(),
            ]
        );
    }
}
