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
use App\Service\languageMenuHelper;
use App\Service\reservationDataHelper;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;


class UserFrontendController extends AbstractController
{
    public function __construct(
                        private DatesRepository $datesRepository,
                        private LangRepository $langRepository,
                        private ReservationRepository $reservationRepository,
                        private TranslatorInterface $translator,
                        private EntityManagerInterface $entityManager,
                        private breadcrumbsHelper $breadcrumbsHelper,
                        private OptionsRepository $optionsRepository,
                        private ReservationOptionsRepository $reservationOptionsRepository,
                        private languageMenuHelper $languageMenuHelper,
                        private reservationDataHelper $reservationDataHelper)
    {
    }

    #[Route(path: '/user', name: 'frontend_user')]
    #[Route(
        path: [
            'en' => '{_locale}/user',
            'es' => '{_locale}/usuario',
            'fr' => '{_locale}/utilisateur'],
        name: 'frontend_user',
        priority: 2)]
    public function frontend_user(
        string $_locale = null,
        string $locale = 'es'
    ) {
        $locale = $_locale ? $_locale : $locale;
        /**
         * @var User $user
         */
        $user = $this->getUser();

        // Swith Locale Loader
        $urlArray = $this->languageMenuHelper->basicLanguageMenu($locale);

        $dates = $this->datesRepository->listNextDates();
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
    #[Route(
        path: [
            'en' => '{_locale}/user/reservations/',
            'es' => '{_locale}/usuario/reservas/',
            'fr' => '{_locale}/utilisateur/reservations/'],
        name: 'frontend_user_reservations',
        priority: 2)]
    public function frontend_user_reservations(
        string $_locale = null,
        string $locale = 'es',
    ) {
        $locale = $_locale ? $_locale : $locale;
        $user = $this->getUser();

        // Swith Locale Loader
        $urlArray = $this->languageMenuHelper->basicLanguageMenu($locale);

        // BREADCRUMBS
        $this->breadcrumbsHelper->userFrontendReservationsBreadcrumbs($locale);

        // END BREADCRUMBS

        $reservations = $this->reservationRepository->findBy(['user' => $user], ['date_ajout' => 'DESC']);


        return $this->render('/user/user_reservations.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'reservations' => $reservations,
        ]);
    }

    #[Route(
        path: [
            'en' => '{_locale}/user/settings',
            'es' => '{_locale}/usuario/datos',
            'fr' => '{_locale}/utilisateur/donnees'],
        name: 'frontend_user_settings')]
    public function frontend_user_settings(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        AuthenticationUtils $authenticationUtils,
        string $_locale = null,
        string $locale = 'es'
    ):Response {
        dump("salut");
        $locale = $_locale ?: $locale;

        // Swith Locale Loader
        $urlArray = $this->languageMenuHelper->basicLanguageMenu($locale);

        $this->breadcrumbsHelper->frontendUserSettingsBreadcrumbs($locale);

        // Create User Form
        /**
         * @var User $user
         */
        $user = $this->getUser();
        dump($user);
        $form = $this->createForm(UserType::class, $user);
        dump($form);
        $form->handleRequest($request);
        dump($authenticationUtils->getLastAuthenticationError());
        if ($form->isSubmitted() && $form->isValid()) {

            $userData = $form->getData();
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form['password']->getData()
                ));
            $userData->setPassword($user->getPassword());
            $this->entityManager->persist($userData);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('Gracias, hemos guardado tus datos correctamente'));

            return $this->redirectToRoute('frontend_user');
        }

        return $this->render('user/user_settings.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'form' => $form->createView(),
        ]);
    }

    /* #[Route(path: '/user/download/document/{document}', name: 'user-download-document2')] */
    /* public function downloadDocument2(
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
    } */

    #[Route(
        path: '/user/ajax/reservation-manager/changeNb/',
        name: 'user-ajax-reservation-manager-add-pilote')]
    public function reservationManagerChangeNb(
        Request $request
    ) {
        $reservationId = $request->request->get('reservationId');
        $operation = $request->request->get('operation');
        $operationArray = explode('-', $operation);
        $reservation = $this->reservationRepository->find($reservationId);
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

    #[Route(
        path: '/user/ajax/reservation-manager/changeOptionAmmount/',
        name: 'user-ajax-reservation-manager-change-option-ammount')]
    public function reservationManagerChangeOptionAmmount(
        Request $request
    ) {
        $reservationId = $request->request->get('reservationId');
        $optionId = $request->request->get('optionId');
        $operation = $request->request->get('operation');
        $operationArray = explode('-', $operation);
        $reservation = $this->reservationRepository->find($reservationId);
        $option = $this->optionsRepository->find($optionId);
        $reservationOption = $this->reservationOptionsRepository->findOneBy([
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

    #[Route(
        path: '/user/ajax/reservation-manager/updateReservation/{reservation}',
        name: 'user-ajax-reservation-manager-update-reservation')]
    public function reservationManagerUpdateReservation(
        Request $request,
        Reservation $reservation
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
                $option = $this->optionsRepository->find($optionId);
                $reservationOptions = $this->reservationOptionsRepository->findOneBy([
                    'reservation' => $reservation,
                    'options' => $option,
                ]);
                if (null !== $reservationOptions) {
                    $reservationOptions->setAmmount($nb);
                    $this->entityManager->persist($reservationOptions);
                } else {
                    $reservationOptions = new ReservationOptions();
                    $reservationOptions->setOption($option);
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