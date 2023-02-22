<?php

namespace App\Controller;

use App\Entity\Codespromo;
use App\Entity\Invoices;
use App\Entity\Reservation;
use App\Entity\ReservationOptions;
use App\Entity\Travellers;
use App\Entity\User;
use App\Form\InvoicesType;
use App\Form\UserType;
use App\Manager\ReservationManager;
use App\Repository\CodespromoRepository;
use App\Repository\DatesRepository;
use App\Repository\InvoicesRepository;
use App\Repository\LangRepository;
use App\Repository\OptionsRepository;
use App\Repository\ReservationOptionsRepository;
use App\Repository\ReservationRepository;
use App\Repository\TravellersRepository;
use App\Repository\TravelTranslationRepository;
use App\Repository\UserRepository;
use App\Service\invoiceHelper;
use App\Service\logHelper;
use App\Service\Mailer;
use App\Service\pdfHelper;
use App\Service\reservationDataHelper;
use App\Service\reservationHelper;
use App\Service\UploadHelper;
use App\Service\travellersHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReservationAjaxController extends AbstractController
{

    public function __construct(
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager,
        private ReservationRepository $reservationRepository,
        private TravelTranslationRepository $travelTranslationRepository,
        private LangRepository $langRepository,
        private reservationHelper $reservationHelper,
        private reservationDataHelper $reservationDataHelper,
        private invoiceHelper $invoiceHelper,
        private DatesRepository $datesRepository,
        private reservationManager $reservationManager,
        private CodespromoRepository $codespromoRepository,
        private travellersHelper $travellersHelper)
    {
    }

   #[Route(
        '/reservation/ajax',
        name: 'reservation_ajax')]
    public function initializeAsLogged(
        Request $request
    ): Response {
        $reservationData = [
            'nbPilotes' => $request->request->get('nbPilotes'),
            'options' => $request->request->get('options'),
            'nbAccomp' => $request->request->get('nbAccomp'),
            'dateId' => $request->request->get('dateId'),
        ];

        $date = $this->datesRepository->find($reservationData['dateId']);
        /**
        * @var User $user
        */
        $user = $this->getUser();
        $reservation = $this->reservationHelper->makeReservation($reservationData, $date, $user, $request->getLocale(), '');
        $this->invoiceHelper->newInvoice(
            $reservation,
            $request->getLocale(),
            [
            'name' => $user->getPrenom().' '.$user->getNom(),
            'address' => $user->getAddress(),
            'nif' => $user->getIdCard(),
            'postalcode' => $user->getPostcode(),
            'city' => $user->getCity(),
            'country' => $user->getCountry(),
            ]);
        $message = $this->translator->trans('Tu reserva ha sido iniciada. Para poder cerrarla puedes pasar a realizar el pago. Gracias');
        $status = null;
        if ($reservation != null) {
            $status = true;
        }
        $html = $this->renderView('reservation/_login_result_card.html.twig', [
            'status' => $status,
            'user' => $user,
            'reservationId' => $reservation->getId(),
        ]);

        return $this->json([
            'code' => 200,
            'message' => $message,
            'reservationId' => $reservation->getId(),
            'reservationDate' => $reservation->getDate()->getId(),
            'nbPilotes' => $reservationData['nbPilotes'],
            'nbAccomp' => $reservationData['nbAccomp'],
            'reservationOptions' => $reservation->getReservationOptions(),
            'user' => $user,
            'userId' => $user ? $user->getId() : '',
            'isInitialized' => true,
            'html' => $html,
        ], 200, [], ['groups' => 'main']);
    }

    #[Route(
        path: '/ajax/initialize/reservation/register/{_locale}',
        options: ['expose' => true],
        methods: ['POST'],
        name: 'initialize-reservation-register')]
    #[Route(
        path: [
            'en' => '/ajax/initialize/reservation/register/{_locale}',
            'es' => '/ajax/initialize/reservation/register/{_locale}',
            'fr' => '/ajax/initialize/reservation/register/{_locale}'],
        methods: ['POST'],
        options: ['expose' => true],
        name: 'initialize-reservation-register')]
    public function initializeAsRegister(
        Request $request,
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $formLoginAuthenticator,
        RememberMeHandlerInterface $rememberMe,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        string $_locale = null,
        string $locale = 'es'
    ) {
        $locale = $locale ? $locale : $_locale;
        if ($request->isXmlHttpRequest()) {
            $userType = new User();
            $form = $this->createForm(UserType::class, $userType);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $userData = $form->getData();

                $user = new User();
                $user->setEmail($userData->getEmail());
                $user->setNom($userData->getNom());
                $user->setPrenom($userData->getPrenom());
                $user->setTelephone($userData->getTelephone());
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $userData->getPassword())
                );
                $user->setRoles(['ROLE_USER']);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $userAuthenticator->authenticateUser(
                    $user,
                    $formLoginAuthenticator,
                    $request
                );
                $rememberMe->createRememberMeCookie($this->getUser());
                // $loggedUserCardHtml = $this->renderView('reservation/usercards/_card_logged_user.html.twig',['isInitialized'=>false]);
                return $this->json([
                    'code' => 200,
                    // 'message' => $loggedUserCardHtml,
                    'message' => '',
                    'user' => $user,
                    'userId' => $user ? $user->getId() : '',
                    'isInitialized' => false,
                    ],
                    200,
                    [],
                    ['groups' => 'main']
                );
            }
            $violations = $validator->validate($userType);

            if (0 !== count($violations->findByCodes(UniqueEntity::NOT_UNIQUE_ERROR))) {
                $userRequestData = $request->request->get('user');
                $user = $userRepository->findBy(
                    [
                        'email' => $userRequestData['email'],
                    ]
                );

                if (null != $user) {
                    $swalHtml = $this->renderview(
                        'reservation/usercards/_swal_login.html.twig',
                        [
                            'email' => $userRequestData['email'],
                        ]
                    );

                    return $this->json([
                        'errorLabel' => $this->translator->trans('Error'),
                        'errorTitle' => $this->translator->trans('Este email ya está tomado'),
                        'errorIntro' => $this->translator->trans('Este email ya está en nuestro sistema, por favor, rellena los campos siguientes'),
                        'html' => $swalHtml,
                        'emailLabel' => $this->translator->trans('Email'),
                        'passwordLabel' => $this->translator->trans('Contraseña'),
                        'takenEmail' => true,
                        'email' => $userRequestData['email'],
                    ], 401);
                }
            }

            $registerFormHtml = $this->renderView('reservation/usercards/_card_register.html.twig', [
                'locale' => $locale,
                /* 'date' => $date, */
                'form' => $form->createView(),
                'isInitialized' => false,
            ]);

            return $this->json([
                        'html' => $registerFormHtml,
                        ],
                401,
                [],
                ['groups' => 'main']);
        }
    }

    #[Route(
        path: '/ajax/initialize/reservation/login/',
        options: ['expose' => true],
        name: 'api_auth_login',
        methods: ['POST'])]
    public function initializeAsLogin(
        TokenStorageInterface $tokenStorage,
        RememberMeHandlerInterface $rememberMe
        ): Response {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if ($user instanceof User) {
            // Create a response object so we can attach the Remember Me Cookie
            // to be sent back to the client.
            $response = $this->json([
                'username' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
                'icon' => 'success',
                'correctLogin' => 'Has entrado correctamente',
            ], 200, [], ['groups' => 'main']);

            $rememberMe->createRememberMeCookie($this->getUser());

            return $response;
        }

        return $this->json([
            'message' => 'Login failed',
            'icon' => 'error',
            'checkPassword' => 'Comprueba tu contraseña',
        ], 401);
    }

    #[Route(
        path: '/ajax/registered/',
        name: 'ajax_registered')]
    public function ajaxRegistered(): Response
    {
        return new response(true);
    }

    #[Route(
        path: '/ajax/reservation/initialize/{_locale}',
        options: ['expose' => true],
        name: 'initialize_reservation')]
    #[Route(
        path: [
            'en' => '/ajax/reservation/initialize/{_locale}',
            'es' => '/ajax/reservation/initialize/{_locale}',
            'fr' => '/ajax/reservation/initialize/{_locale}'],
        options: ['expose' => true],
        name: 'initialize_reservation')]
    public function initializeReservation(
        Request $request,
        string $_locale = null,
        string $locale = 'es'): Response
    {
        $locale = $_locale ? $_locale : $locale;
        $renderArray = [];
        $requestData = [
            'dateId' => $request->request->get('dateId'),
            'nbPilotes' => $request->request->get('nbPilotes'),
            'nbAccomp' => $request->request->get('nbAccomp'),
            'options' => $request->request->get('options'),
            'userId' => $request->request->get('user'),
        ];

        $date = $this->datesRepository->find($requestData['dateId']);
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $isReservedObject = $this->reservationRepository->findOneBy([
            'user' => $user,
            'date' => $date,
        ]);
        if (null !== $isReservedObject) {
            $url = $this->generateUrl('frontend_user_reservation', [
                'reservation' => $isReservedObject->getId(),
                '_locale' => $locale,
            ]);
            $messageHtml = $this->renderView('reservation/_partials/_reservation_initialized.html.twig',['url'=> $url]);
            return $this->json([
                'isReserved' => true,
                'message' => $messageHtml,
                'user' => $user,
                'userId' => $user ? $user->getId() : '',
                'reservationId' => $isReservedObject->getId(),
            ], 403, [], ['groups' => 'main']);
        }
        $codepromo = $this->codespromoRepository
                        ->findOneBy([
                            'email' => $user->getEmail(),
                        ]);
        if (null == $codepromo) {
            $codepromo = $this->codespromoRepository
                            ->findOneBy([
                                'user' => $user,
                            ]);
        }

        $reservation = $this->reservationManager->sendReservation($requestData, $date, $user, $request->getLocale());

        // GET TRANSLATION FOR TRAVEL $travelTranslation
        $lang = $this->langRepository->findOneBy(['iso_code' => $locale]);
        $travelTranslation = $this->travelTranslationRepository->findOneBy([
            'lang' => $lang,
            'travel' => $reservation->getDate()->getTravel(),
        ]);
        // CREATE RESERVATION OPTIONS ARRAY
        $reservationOptions = $this->reservationHelper->getReservationOptions($reservation, $locale);

        $swalHtml = $this->renderView('reservation/_swal_confirmation_message.html.twig',
            [
                'codepromo' => $codepromo,
                '_locale' => $locale,
            ]);
        $renderHtml = $this->renderView('reservation/usercards/_card_logged_user.html.twig',
            [
                'isInitialized' => true,
                'reservationOptions' => $reservationOptions,
                'reservation' => $reservation, ]);
        $codespromoHtml = $this->renderView('reservation/cards/_card_codespromo.html.twig', [
            'user' => $this->getUser(),

        ]);
        $renderArray = [
            'code' => 200,
            'lang' => $lang->getId(),
            'reservationId' => $reservation->getId(),
            'reservationDate' => $reservation->getDate()->getId(),
            'reservationTravelTitle' => $travelTranslation->getTitle(),
            'nbPilotes' => $requestData['nbPilotes'],
            'nbAccomp' => $requestData['nbAccomp'],
            'reservationOptions' => $reservationOptions,
            'options' => $reservationOptions,
            'user' => $user,
            'userId' => $user ? $user->getId() : '',
            'isInitialized' => true,
            'swalHtml' => $swalHtml,
            'codespromoHtml' => $codespromoHtml,
            'html' => $renderHtml,
            'locale' => $locale,
        ];
        if ($codepromo !== null) {
            $renderArray = array_merge($renderArray,
                [
                    'codepromo' => $codepromo->getCode(),
                    'codepromoId' => $codepromo->getId(),
                    'codepromoMontant' => $codepromo->getMontant(),
                    'codepromoPourcentage' => $codepromo->getPourcentage(),
                    'codepromoDebut' => $codepromo->getDebut(),
                    'codepromoFin' => $codepromo->getFin(),
                ]
            );
        }

        return $this->json($renderArray, 200, [], ['groups' => 'main']);
    }

    #[Route(
        path: '/ajax/load-user-switch/{_locale}',
        options: ['expose' => true],
        name: 'ajax_load_user_switch')]
    #[Route(
        path: [
            'en' => '/ajax/load-user-switch/{_locale}',
            'es' => '/ajax/load-user-switch/{_locale}',
            'fr' => '/ajax/load-user-switch/{_locale}'],
        options: ['expose' => true],
        name: 'ajax_load_user_switch')]
    public function ajaxLoadUserSwitch(
        string $_locale = null,
        string $locale = 'es'): Response
    {
        $locale = $_locale ? $_locale : $locale;
        return $this->render('shared/_user_switch.html.twig',['locale' => $locale]);
    }

    #[Route(
            path: '/ajax/login-result/{_locale}',
        options: ['expose' => true],
        name: 'ajax_login_result',
        methods: ['POST'])]
    #[Route(
        path: [
            'en' => '/ajax/login-result/{_locale}',
            'es' => '/ajax/login-result/{_locale}',
            'fr' => '/ajax/login-result/{_locale}'],
        options: ['expose' => true],
        name: 'ajax_login_result',
        methods: ['POST'])]
    public function ajaxLoginResult(
        Request $request,
        string $_locale = null,
        string $locale = "es"
        ): Response {
            $locale = $_locale ? $_locale : $locale;
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $reservationId = $request->request->get('reservationId');
        if (null !== $reservationId) {
            // ASSIGN USER TO RESERVATION
            $reservation = $this->reservationRepository->find($reservationId);

            $reservation->setUser($user);
            $reservation->setStatus('assigned');
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
        }

        return $this->render('reservation/_login_result_card.html.twig', [
            'user' => $user,
            'reservationId' => $reservationId,
            'locale' => $locale
        ]);
    }

    #[Route(
        path: '/ajax/assign/codepromo/{reservation}/{codepromo}',
        options: ['expose' => true],
        methods: ['GET'],
        name: 'assign-codepromo')]
    public function ajaxAssignCodepromo(
        Codespromo $codepromo,
        Reservation $reservation):Response
    {
        $reservation->setCodespromo($codepromo);
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return new Response(true);
    }

    #[Route(
        path: '/ajax/invoice/{invoice}',
        methods: ["GET","POST"],
        options: ['expose' => true],
        name: 'ajax_invoice')]
    public function ajaxInvoice(
        Request $request,
        Invoices $invoice
        ): Response
    {
        $locale = $request->request->get('locale');
        $formInvoice = $this->createForm(InvoicesType::class, $invoice);
        $html = $this->renderView('user/partials/_card_reservation_invoice.html.twig', [
            'invoice' => $invoice,
            'locale' => $locale,
            'formInvoice' => $formInvoice->createView(),
        ]);

        return $this->json([
                'title' => $this->translator->trans('Completar datos de facturación',[],null, $locale),
                'html' => $html,
                'confirmButtonText' => $this->translator->trans('Actualizar factura',[],null, $locale)
            ], 200, [], ['group' => 'main']);
    }

    #[Route(
        path: '/ajax/save-invoice/{invoice}',
        options: ['expose' => true],
        name: 'ajax_save_invoice')]
    public function ajaxSaveInvoice(
            Request $request,
            Invoices $invoice):Response
    {
        $customerData = $request->request->all();
        try { $invoiceStatus = $this->invoiceHelper->updateInvoiceBillingData(
            $invoice,
            'es',
            $customerData);
        } catch(\Exception $e) {
            echo $e->getMessage();
        }
        $html = $this->renderView('user/partials/_row_user_invoices.html.twig', [
            'invoice' => $invoice,
        ]);

        return $this->json([
            'status' => 200,
            'message' => 'The pdf has been saved',
            'html' => $html,
        ], 200);

        return new Response('The pdf has been saved');
    }

    #[Route(
        path: '/ajax/add-travellers/{reservation}',
        options: ['expose' => true],
        name: 'ajax-add-travellers')]
    public function ajaxAddTravellers(
        Request $request,
        Reservation $reservation
    ) {
        /**
         * @var array $travellersArray
         */
        $travellersArray = $request->request->all();
        foreach ($travellersArray['traveller'] as $travellerData) {
            $this->travellersHelper->addTravellerToReservation(
                                        $travellerData,
                                        $reservation,
                                        $this->getUser());
        }

        $travellersTableHtml = $this->renderView('reservation/_travellers_table.html.twig', [
            'travellers' => $reservation->getTravellers(),
        ]);
        dump($travellersTableHtml);
        return $this->json([
            'travellersTableHtml' => $travellersTableHtml,
            'travellersByReservation' => $reservation->getTravellers(),
            ], 200, [], ['groups' => 'main']);
    }

    #[Route(
        path: '/ajax/add/user',
        options: ['expose' => true],
        name: 'ajax-assign-user')]
    public function ajaxAssignUser()
    {
        /**
        * @var User $user
        */
        $user = $this->getUser();

        return $this->json([
            'user' => [
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'telephone' => $user->getTelephone(),
                ],
            ],
            200,
            [],
            ['groups' => 'main']
        );
    }

    #[Route(
        path: '/ajax/cancel/reservation',
        options: ['expose' => true],
        methods: ['POST'],
        name: 'ajax-cancel-reservation')]
    public function ajaxCancelReservation(
            Request $request,
            ReservationRepository $reservationRepository):Response
    {
        $reservation = $reservationRepository
                        ->find($request->request->get('reservationId'));
        $reservation->setStatus('cancelled');
        $this->entityManager->flush();
        $customerData = [];
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $this->invoiceHelper->cancelInvoice($reservation, $request->get('locale'), $customerData);
        $this->invoiceHelper->createCancelationInvoice($reservation);

        return new Response('Cancel Reservation');
    }

    #[Route(
        path: '/ajax/reactivate/reservation',
        options: ['expose' => true],
        methods: ['POST'],
        name: 'ajax-reactivate-reservation')]
    public function ajaxReactivateReservation(
        Request $request,
        ReservationRepository $reservationRepository
        ):Response {
        $reservation = $reservationRepository->find($request->request->get('reservationId'));
        $reservation->setStatus('initialized');
        $this->entityManager->flush();

        return new Response('Reactivate Reservation');
    }

    /* #[Route(
        path: 'ajax/reservation/setStatus/{reservation}',
        options: ['expose' => true],
        methods: ['POST'],
        name: 'ajax_reservation_set_status')]
    public function ajaxReservationSetStatus(
        Request $request,
        Reservation $reservation,
        TranslatorInterface $translator
    ) {
        $previousStatus = $reservation->getStatus();

        $newStatus = $request->request->get('status');
        /**
         * @var User @user
         */
        /*$user = $this->getUser();
        $customerData = [
            'name' => $user->getPrenom().' '.$user->getNom(),
            'address' => $user->getAddress(),
            'nif' => $user->getIdCard(),
            'postalcode' => $user->getPostcode(),
            'city' => $user->getCity(),
            'country' => $user->getCountry(),
        ];

        if ($newStatus != $previousStatus) {
            $this->invoiceHelper->makeInvoice($reservation, $newStatus, $customerData, $request->getLocale());
            $reservation->setStatus($newStatus);
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();

            if ($newStatus == 'initialized') {
                return $this->json([
                    'status' => 200,
                    'newStatus' => $newStatus,
                    'previousStatus' => $previousStatus,
                    'message' => $translator->trans('Esta reserva se ha cambiado a inicializada'),
                    'label' => $translator->trans('Cancelar esta reserva'),
                ], 200);
            }

            return $this->json([
                'status' => 200,
                'newStatus' => $newStatus,
                'previousStatus' => $previousStatus,
                'message' => $translator->trans('Esta reserva se ha cambiado a cancelada'),
                'label' => $translator->trans('Reinicializar esta reserva'),
            ], 200);
        }

        return $this->json([
                'status' => 401,
                'newStatus' => $newStatus,
                'previousStatus' => $previousStatus,
                'message' => $translator->trans('no ha de ser cambiada'),
            ], 401);

        /* return $this->json([
        'status' => 200,
        'response' => $translator->trans('Esta reserva se ha cambiado a cancelada')
        ],200); */
    /*} */

    #[Route(
        path: 'ajax/update/changes/{reservation}',
        options: ['expose' => true],
        name: 'ajax-update-changes',
        methods: ['POST'])]
    public function ajaxUpdateChanges(
        Request $request,
        Reservation $reservation,
    ) {
        $reservationData = [
            'nbpilotes' => $request->request->get('nbpilotes'),
            'nbaccomp' => $request->request->get('nbaccomp'),
            'finalOptions' => $request->request->get('options')
        ];
        dump($reservationData['finalOptions']);
        /**
          * @var User $user
          */
        $user = $this->getUser();
        $customerData = [
            'name' => $user->getPrenom().' '.$user->getNom(),
            'address' => $user->getAddress(),
            'nif' => $user->getIdCard(),
            'postalcode' => $user->getPostcode(),
            'city' => $user->getCity(),
            'country' => $user->getCountry(),
        ];

        $reservation = $this->reservationHelper->updateReservation(
            $reservation,
            $reservationData,
            $customerData,
            $request->getLocale());
        dump($reservation);
        $html = $this->renderView('user/partials/_card_reservation_updated.html.twig', ['reservation' => $reservation]);

        return $this->json([
            'html' => $html,
        ], 200);
    }

    #[Route(path: 'ajax/reservation/applyCodePromo/{codepromo}', options: ['expose' => true], name: 'apply-code-promo', methods: ['POST'])]
    public function applyCodePromo(
        Request $request,
        Codespromo $codepromo,
        ReservationRepository $reservationRepository,
        reservationHelper $reservationHelper)
    {
        $reservationId = $request->request->get('reservation');
        $reservation = $reservationRepository->find($reservationId);
        $reservation->setCodespromo($codepromo);
        $this->entityManager->persist($reservation);

        $totalAmmount = $this->reservationDataHelper->getReservationAmmountBeforeDiscount($reservation);
        $now = new \DateTime();
        if ($codepromo->getType() == 'uses') {
            $codepromo->setNombre($codepromo->getNombre() - 1);
            if ($codepromo->getNombre() == 0) {
                $codepromo->setStatut('Inactif');
            }
            $this->entityManager->persist($codepromo);
        } elseif ($codepromo->getType() == 'period') {
            if ($codepromo->getDebut() <= $now && $now <= $codepromo->getFin()) {
                dd('valid period');
            }
        }

        $this->entityManager->flush();
        if ($codepromo->getMontant() != null && $codepromo->getPourcentage() == null) {
            $discountedAmmount = $totalAmmount - $codepromo->getMontant();
        }
        if ($codepromo->getPourcentage() != null && $codepromo->getMontant() == null) {
             $discountedAmmount = $totalAmmount * (1 - $codepromo->getPourcentage() / 100);
        }

        return $this->json([
            'status' => 200,
            'message' => 'hello',
            'codeTitle' => $codepromo->getCode(),
            'codeMontant' => $codepromo->getMontant(),
            'codePourcentage' => $codepromo->getPourcentage(),
            'totalAmmount' => $totalAmmount,
            'discountedAmmount' => $discountedAmmount,
        ], 200);
    }

    #[Route(
        path: 'ajax/update-calculator/{_locale}',
        methods: ['POST', 'GET'],
        name: 'update-calculator',
        options: ['expose' => true])]
    #[Route(
        path: [
            'en' => 'ajax/update-calculator/{_locale}',
            'es' => 'ajax/actualizar-calculadora/{_locale}',
            'fr' => 'ajax/actualiser-calculatrice/{_locale}'],
        methods: ['POST', 'GET'],
        name: 'update-calculator',
        options: ['expose' => true])]
    public function updateCalculator(
        Request $request,
        string $locale = 'es',
        $_locale = null
    ): Response {
        $locale = $_locale ? $_locale : $locale;

        $data = $request->request->all();
        dump($data);
        $date = $this->datesRepository->find($data['dateId']);
        $renderArray = [
            'date' => $date,
            'locale' => $_locale,
            '_locale' => $locale,
        ];

        $renderArray = array_merge($renderArray, $data);

        if (isset($data['reservation'])) {
            dump($data['reservation']);
            $reservation = $this->reservationRepository->find($data['reservation']);
            dump($reservation);
            $renderArray['reservation'] = $reservation;
        }

        $renderArray['options'] = (isset($data['options'])) ? $data['options'] : '';
        $renderArray['reservationOptions'] = (isset($data['options'])) ? $data['options'] : '';
        $renderArray['optionsJson'] = (isset($data['options'])) ? json_encode($data['options']) : '';
        $renderArray['isInitialized'] = (isset($data['isInitialized'])) ? $data['isInitialized'] : '';
        $renderArray['userEdit'] = (isset($data['userEdit'])) ? $data['userEdit'] : '';

        return $this->render('reservation/_wrapper_calculator_logged_user.html.twig', $renderArray);
    }

    #[Route(
        path: 'ajax/add-codespromo',
        methods: ['POST'],
        name: 'reservation-addCodesPromo',
        options: ['expose' => true] )]
    public function reservationAddCodesPromo(
        Request $request
    ):Response{
        $reservationId = $request->request->get('reservationId');

        $codespromoText = $request->request->get('codespromo');
        $codespromo = $this->codespromoRepository->findByCode($codespromoText);
        $reservation = $this->reservationRepository->find($reservationId);

        $reservation->setCodesPromo($codespromo[0]);
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();
        return new Response('hello '.$reservationId.' , '.$codespromoText);
    }

    #[Route(
        path: 'ajax/addTravellersForms/{_locale}',
        methods: ['POST'],
        name: 'add-travellers-forms',
        options: ['expose' => true] )]
        #[Route(
            path: [
                'en' => '/ajax/addTravellersForms/{_locale}',
                'es' => '/ajax/addTravellersForms/{_locale}',
                'fr' => '/ajax/addTravellersForms/{_locale}'],
            options: ['expose' => true],
            methods: ['POST'],
            name: 'add-travellers-forms')]
        function addTravellersForms(
            Request $request
        ){
            $reservationId = $request->request->get('reservation');
            dump($reservationId);
            $renderArray = [];
            $renderArray['nbpilotes'] = $request->request->get('nbpilotes');
            $renderArray['nbaccomp'] = $request->request->get('nbaccomp');
            if(isset($reservationId) ) {
                $reservation = $this->reservationRepository->find($reservationId);
                dump(count($reservation->getTravellers()));
                foreach ($reservation->getTravellers() as $traveller){
                    dump($traveller);
                }
                $renderArray['reservation'] = $reservation;
            }

            $i = 0;
            $html = $this->renderView(
                    'reservation/cards/_card_add_travellers_data.html.twig',
                    $renderArray
                );
            return $this->json(['html' => $html], 200, [],[]);
            //return new Response($html);
        }

    #[Route(
        path: 'ajax/edit/traveller/{traveller}',
        name: 'ajax-edit-traveller',
        methods: ['POST', 'GET'],
        options: ['expose' => true]
    )]

    public function ajaxEditTraveller(
        Request $request,
        Travellers $traveller) {
            $swalHtml = $this->renderView(
                'reservation/swal/_swal_edit_traveller.html.twig',
                ['traveller' => $traveller]);
            return $this->json(
                ['swalHtml' => $swalHtml],
                200,
                [],
                []);
        }

    #[Route(
        path: "ajax/save/traveller/{traveller}",
        name: "ajax-save-traveller",
        methods: ["POST","GET"],
        options: ["expose" => true ]
    )]

    public function ajaxSaveTraveller(
        Request $request,
        Travellers $traveller
    ) {
        $data = $request->request->all();
        $this->travellersHelper->updateTravellerData($traveller,$data );
        $html = $this->renderView('reservation/_travellers_row.html.twig',['traveller' =>$traveller]);
        return $this->json(['html' => $html],200,[],[]);
    }

    #[Route(
        path: "ajax/update/reservation/{reservation}",
        name: "ajax-update-reservation",
        methods: ["POST","GET"],
        options: ["expose" => true ]
    )]
    function ajaxUpdateReservation(
        Request $request,
        Reservation $reservation
    ) : Response {
        return new Response(true);
    }
}
