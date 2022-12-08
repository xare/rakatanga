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
use App\Service\reservationHelper;
use App\Service\UploadHelper;
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
    private TranslatorInterface $translator;
    private LangRepository $langRepository;
    private EntityManagerInterface $entityManager;
    private reservationHelper $reservationHelper;
    private invoiceHelper $invoiceHelper;

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        LangRepository $langRepository,
        reservationHelper $reservationHelper,
        invoiceHelper $invoiceHelper)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->langRepository = $langRepository;
        $this->reservationHelper = $reservationHelper;
        $this->invoiceHelper = $invoiceHelper;
    }

   #[Route('/reservation/ajax', name: 'reservation_ajax')]
      /**
       * @Route("/ajax/initialize/reservation/logged",
       * options = { "expose" = true },
       * name="initialize_reservation_logged")
       * )
       */
      public function initializeAsLogged(
        Request $request,
        logHelper $logHelper,
        DatesRepository $datesRepository,
        reservationHelper $reservationHelper,
        InvoiceHelper $invoiceHelper,
        TranslatorInterface $translator
    ): Response {
          $reservationData = [
              'nbPilotes' => $request->request->get('nbPilotes'),
              'options' => $request->request->get('options'),
              'nbAccomp' => $request->request->get('nbAccomp'),
              'dateId' => $request->request->get('dateId'),
          ];

          $date = $datesRepository->find($reservationData['dateId']);
          /**
           * @var User $user
           */
          $user = $this->getUser();
          $reservation = $reservationHelper->makeReservation($reservationData, $date, $user, $request->getLocale(), '');
          $invoiceHelper->newInvoice($reservation, [
              'name' => $user->getPrenom().' '.$user->getNom(),
              'address' => $user->getAddress(),
              'nif' => $user->getIdCard(),
              'postalcode' => $user->getPostcode(),
              'city' => $user->getCity(),
              'country' => $user->getCountry(),
          ], $request->getLocale());
          $message = $translator->trans('Tu reserva ha sido iniciada. Para poder cerrarla puedes pasar a realizar el pago. Gracias');
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

  #[Route(path: '/ajax/initialize/reservation/register/', options: ['expose' => true], name: 'initialize-reservation-register')]
    public function initializeAsRegister(
        Request $request,
        logHelper $logHelper,
        DatesRepository $datesRepository,
        reservationHelper $reservationHelper,
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $formLoginAuthenticator,
        RememberMeHandlerInterface $rememberMe,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        $locale = 'es'
    ) {
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
                        'passwordLabel' => $this->translator->trans('Password'),
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

     #[Route(path: '/ajax/initialize/reservation/login/', options: ['expose' => true], name: 'api_auth_login', methods: ['POST'])]
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

    #[Route(path: '/ajax/registered/', name: 'ajax_registered')]
    public function ajaxRegistered(): Response
    {
        return new response(true);
    }

    #[Route(path: '/ajax/reservation/initialize/{_locale}', options: ['expose' => true], name: 'initialize_reservation')]
    #[Route(path: ['en' => '/ajax/reservation/initialize/{_locale}', 'es' => '/ajax/reservation/initialize/{_locale}', 'fr' => '/ajax/reservation/initialize/{_locale}'], options: ['expose' => true], name: 'initialize_reservation')]
    public function initializeReservation(
        Request $request,
        DatesRepository $datesRepository,
        ReservationRepository $reservationRepository,
        reservationHelper $reservationHelper,
        logHelper $logHelper,
        LangRepository $langRepository,
        TravelTranslationRepository $travelTranslationRepository,
        CodespromoRepository $codespromoRepository,
        ReservationManager $reservationManager,
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

        $date = $datesRepository->find($requestData['dateId']);
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $isReservedObject = $reservationRepository->findOneBy([
            'user' => $user,
            'date' => $date,
        ]);
        if (null !== $isReservedObject) {
            $url = $this->generateUrl('frontend_user_reservation', [
                'reservation' => $isReservedObject->getId(),
                '_locale' => $locale,
            ]);

            return $this->json([
                'isReserved' => true,
                'message' => $this->translator->trans('Esta reserva ya ha sido realizada, acude a tu espacio de usuario para continuar con tu proceso de reserva. <a href="'.$url.'">Tu reserva</a>'),
                'user' => $user,
                'userId' => $user ? $user->getId() : '',
                'reservationId' => $isReservedObject->getId(),
            ], 403, [], ['groups' => 'main']);
        }
        $codepromo = $codespromoRepository
                         ->findOneBy([
                             'email' => $user->getEmail(),
                         ]);
        if (null == $codepromo) {
            $codepromo = $codespromoRepository
                            ->findOneBy([
                                'user' => $user,
                            ]);
        }

        $reservation = $reservationManager->sendReservation($requestData, $date, $user, $request->getLocale());

        // GET TRANSLATION FOR TRAVEL $travelTranslation
        $lang = $langRepository->findOneBy(['iso_code' => $locale]);
        $travelTranslation = $travelTranslationRepository->findOneBy([
            'lang' => $lang,
            'travel' => $reservation->getDate()->getTravel(),
        ]);
        // CREATE RESERVATION OPTIONS ARRAY
        $reservationOptions = $reservationHelper->getReservationOptions($reservation, $lang);

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

    #[Route(path: '/ajax/load-user-switch', options: ['expose' => true], name: 'ajax_load_user_switch')]
    public function ajaxLoadUserSwitch(): Response
    {
        return $this->render('shared/_user_switch.html.twig');
    }

    #[Route(path: '/ajax/login-result', options: ['expose' => true], name: 'ajax_login_result', methods: ['POST'])]
    public function ajaxLoginResult(
        Request $request,
        ReservationRepository $reservationRepository
        ): Response {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $reservationId = $request->request->get('reservationId');
        if (null !== $reservationId) {
            // ASSIGN USER TO RESERVATION
            $reservation = $reservationRepository->find($reservationId);

            $reservation->setUser($user);
            $reservation->setStatus('assigned');
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
        }

        return $this->render('reservation/_login_result_card.html.twig', [
            'user' => $user,
            'reservationId' => $reservationId,
        ]);
    }

    #[Route(path: '/ajax/assign/codepromo/{reservation}/{codepromo}', options: ['expose' => true], methods: ['GET'], name: 'assign-codepromo')]
    public function ajaxAssignCodepromo(
        Codespromo $codepromo,
        Reservation $reservation)
    {
        $reservation->setCodespromo($codepromo);
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return new Response(true);
    }

    #[Route(path: '/ajax/invoice/{invoice}', options: ['expose' => true], name: 'ajax_invoice')]
    public function ajaxInvoice(
        Invoices $invoice
        ): Response {
        $formInvoice = $this->createForm(InvoicesType::class, $invoice);
        $html = $this->renderView('user/partials/_card_reservation_invoice.html.twig', [
            'invoice' => $invoice,
            'formInvoice' => $formInvoice->createView(),
        ]);

        return $this->json([
                'title' => $this->translator->trans('Completar datos de facturación'),
                'html' => $html,
            ], 200, [], ['group' => 'main']);
    }

    #[Route(path: '/ajax/save-invoice/{invoice}', options: ['expose' => true], name: 'ajax_save_invoice')]
    public function ajaxSaveInvoice(
            Request $request,
            Invoices $invoice,
            pdfHelper $pdfHelper,
            UploadHelper $uploadHelper,
            Mailer $mailer,
            InvoicesRepository $invoicesRepository,
            invoiceHelper $invoiceHelper)
    {
        $customerData = $request->request->all();
        $invoiceStatus = $invoiceHelper->replaceInvoice($invoice, $customerData);

        // SEND BY EMAIL
        /* if ($invoiceStatus['new'] == true ){
            $mailer->sendInvoiceToCustomer(
                $invoiceStatus['pdf'],
                $invoice->getReservation(),
                $invoiceStatus['number']);
            return new Response( "The pdf has been saved" );
        } else {
            return new Response( "The invoice has already been created");
        } */

        $html = $this->renderView('user/_row_user_invoices.html.twig', [
            'invoice' => $invoice,
        ]);

        return $this->json([
            'status' => 200,
            'message' => 'The pdf has been saved',
            'html' => $html,
        ], 200);

        return new Response('The pdf has been saved');
    }

    #[Route(path: '/ajax/add-travellers/{reservation}', options: ['expose' => true], name: 'ajax-add-travellers')]
    public function ajaxAddTravellers(
        Reservation $reservation,
        TravellersRepository $travellersRepository
    ) {
        /**
         * @var array $travellersArray
         */
        $travellersArray = [];
        foreach ($travellersArray['traveller'] as $data) {
            $traveller = new Travellers();
            $now = new \DateTime();
            $traveller->setPrenom($data['prenom']);
            $traveller->setNom($data['nom']);
            $traveller->setEmail($data['email']);
            $traveller->setTelephone($data['telephone']);
            $traveller->setPosition($data['position']);
            $traveller->setUser($this->getUser());
            $traveller->setDateAjout($now);
            $traveller->addReservation($reservation);
            $this->entityManager->persist($traveller);
        }
        $this->entityManager->flush();

        $travellersTableHtml = $this->renderView('reservation/_travellers_table.html.twig', [
            'travellers' => $reservation->getTravellers(),
        ]);

        return $this->json([
            'travellersTableHtml' => $travellersTableHtml,
            'travellersByReservation' => $reservation->getTravellers(),
            ], 200, [], ['groups' => 'main']);
    }

    #[Route(path: '/ajax/add/user', options: ['expose' => true], name: 'ajax-assign-user')]
    public function ajaxAssignUser()
    {
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

    #[Route(path: '/ajax/cancel/reservation', options: ['expose' => true], methods: ['POST'], name: 'ajax-cancel-reservation')]
    public function ajaxCancelReservation(
            Request $request,
            ReservationRepository $reservationRepository,
            InvoiceHelper $invoiceHelper)
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

        $this->invoiceHelper->cancelInvoice($reservation->getInvoice());
        $this->invoiceHelper->createCancelationInvoice($reservation);

        return new Response('Cancel Reservation');
    }

    #[Route(path: '/ajax/reactivate/reservation', options: ['expose' => true], methods: ['POST'], name: 'ajax-reactivate-reservation')]
    public function ajaxReactivateReservation(
        Request $request,
        ReservationRepository $reservationRepository
         ) {
        $reservation = $reservationRepository->find($request->request->get('reservationId'));
        $reservation->setStatus('initialized');
        $this->entityManager->flush();

        return new Response('Reactivate Reservation');
    }

    #[Route(path: 'ajax/reservation/setStatus/{reservation}', options: ['expose' => true], methods: ['POST'], name: 'ajax_reservation_set_status')]
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
        $user = $this->getUser();
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
    }

    #[Route(path: 'ajax/update/changes/{reservation}', options: ['expose' => true], name: 'ajax-update-changes', methods: ['POST'])]
     public function ajaxUpdateChanges(
        Request $request,
        Reservation $reservation,
        ReservationRepository $reservationRepository,
        OptionsRepository $optionsRepository,
        ReservationOptionsRepository $reservationOptionsRepository
     ) {
         $requestData = [
             'nbPilotes' => $request->request->get('nbPilotes'),
             'nbAccomp' => $request->request->get('nbAccomp'),
             'options' => $request->request->get('options'),
         ];

         $reservationRepository->find($reservation->getId());

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

         // $invoiceHelper->updateInvoice($reservation,'updated',$customerData,$request->getLocale(),$requestData);
         $reservation->setNbpilotes($requestData['nbPilotes']);
         $reservation->setNbAccomp($requestData['nbAccomp']);

         if ($requestData['options'] != null){
            if(count($requestData['options']) > 0) {
             foreach ($requestData['options'] as $optionArray) {
                 $option = $optionsRepository->find($optionArray['id']);
                 $reservationOptions = $reservationOptionsRepository->findBy(['options' => $optionArray['id']]);

                 if (count($reservationOptions) == 0) {
                     $reservationOption = new ReservationOptions();
                     $reservationOption->setAmmount($optionArray['ammount']);
                     $reservationOption->setOptions($option);

                     $reservation->addReservationOption($reservationOption);
                 } else {
                     foreach ($reservationOptions as $reservationOption) {
                         $reservationOption->setAmmount($optionArray['ammount']);
                         $reservationOption->setOptions($option);

                         $reservation->addReservationOption($reservationOption);
                     }
                 }
             }
            }
        }
         $this->entityManager->persist($reservation);
         $this->entityManager->flush();

         $this->invoiceHelper->updateReservationInvoice($reservation, $request->getLocale(), $customerData);

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
         $reservation->addCodespromo($codepromo);
         $this->entityManager->persist($reservation);

         $totalAmmount = $reservationHelper->getReservationAmmountBeforeDiscount($reservation);
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

     #[Route(path: 'ajax/update-calculator/{_locale}', methods: ['POST', 'GET'], name: 'update-calculator', options: ['expose' => true])]
      #[Route(path: ['en' => 'ajax/update-calculator/{_locale}', 'es' => 'ajax/actualizar-calculadora/{_locale}', 'fr' => 'ajax/actualiser-calculatrice/{_locale}'], methods: ['POST', 'GET'], name: 'update-calculator', options: ['expose' => true])]
      public function updateCalculator(
        Request $request,
        ReservationRepository $reservationRepository,
        DatesRepository $datesRepository,
        string $locale = 'es',
        $_locale = null
      ): Response {
          $locale = $_locale ? $_locale : $locale;

          $data = $request->request->all();

          $date = $datesRepository->find($data['dateId']);
          $renderArray = [
              'date' => $date,
              'locale' => $_locale,
              '_locale' => $locale,
          ];

          $renderArray = array_merge($renderArray, $data);

          if (isset($data['reservation'])) {
              $reservation = $reservationRepository->find($data['reservation']);
              $renderArray['reservation'] = $reservation;
          }

          $renderArray['options'] = (isset($data['options'])) ? $data['options'] : '';
          $renderArray['reservationOptions'] = (isset($data['options'])) ? $data['options'] : '';
          $renderArray['optionsJson'] = (isset($data['options'])) ? json_encode($data['options']) : '';
          $renderArray['isInitialized'] = (isset($data['isInitialized'])) ? $data['isInitialized'] : '';
          $renderArray['userEdit'] = (isset($data['userEdit'])) ? $data['userEdit'] : '';

          return $this->render('reservation/_wrapper_calculator_logged_user.html.twig', $renderArray);
      }
}
