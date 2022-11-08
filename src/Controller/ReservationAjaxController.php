<?php

namespace App\Controller;

use App\Entity\Codespromo;
use App\Entity\Invoices;
use App\Entity\Reservation;
use App\Entity\Travellers;
use App\Entity\User;
use App\Form\InvoicesType;
use App\Form\UserType;
use App\Manager\ReservationManager;
use App\Repository\DatesRepository;
use App\Repository\InvoicesRepository;
use App\Repository\LangRepository;
use App\Repository\ReservationOptionsRepository;
use App\Repository\ReservationRepository;
use App\Repository\TravellersRepository;
use App\Repository\TravelRepository;
use App\Repository\TravelTranslationRepository;
use App\Repository\UserRepository;
use App\Repository\CodespromoRepository;
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
    private $translator; 
    private $langRepository;

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        LangRepository $langRepository)
        {
            $this->translator = $translator;
            $this->em = $em;
            $this->langRepository = $langRepository;
        }
   #[Route('/reservation/ajax', name: 'reservation_ajax')]
    /* public function index(): Response
    {
        return $this->render('reservation_ajax/index.html.twig', [
            'controller_name' => 'ReservationAjaxController',
        ]);
    }  */

    /**
      * @Route("/ajax/initialize/reservation/logged",
      * options = { "expose" = true }, 
      * name="initialize_reservation_logged")
      * )
      */

      public function initializeAsLogged(
        Request $request,
        logHelper $logHelper,
        EntityManagerInterface $em,
        DatesRepository $datesRepository,
        reservationHelper $reservationHelper,
        InvoiceHelper $invoiceHelper,
        TranslatorInterface $translator
    ) {

      $reservationData = [
          'nbPilotes' => $request->request->get('nbPilotes'),
          'options' => $request->request->get('options'),
          'nbAccomp' => $request->request->get('nbAccomp'),
          'dateId' => $request->request->get('dateId')
      ];

      $date = $datesRepository->find($reservationData['dateId']);
      $user = $this->getUser();
      $reservation = $reservationHelper->makeReservation($reservationData, $date, $user, $request->getLocale());
      $invoiceHelper->makeInvoice($reservation, '', [
          'name' => $user->getPrenom(). ' '.$user->getNom(),
          'address' => $user->getAddress(),
          'nif' => $user->getIdCard(),
          'postalcode' => $user->getPostcode(),
          'city' => $user->getCity(),
          'country' => $user->getCountry()
      ], $request->getLocale());
      $message = $translator->trans('Tu reserva ha sido iniciada. Para poder cerrarla puedes pasar a realizar el pago. Gracias');
      $status = null;
      if ($reservation != null){
         $status = true;
      }
      $html=$this->renderView('reservation/_login_result_card.html.twig', [
          'status' => $status,
          'user' => $user,
          'reservationId' => $reservation->getId()
      ]);

      return $this->json([
          'code'=> 200,
          'message' => $message,
          'reservationId' => $reservation->getId(),
          'reservationDate' => $reservation->getDate()->getId(),
          'nbPilotes' => $reservationData['nbPilotes'],
          'nbAccomp' => $reservationData['nbAccomp'],
          'reservationOptions' => $reservation->getReservationOptions(),
          'user' => $user,
          'userId' => $user ? $user->getId() : '',
          'isInitialized' => true,
          'html' => $html
      ], 200,[],['groups'=>'main']);
  
  }

  /**
     * @Route("/ajax/initialize/reservation/register/", 
     * options = { "expose" = true }, 
     * name="initialize-reservation-register")
     */
    public function initializeAsRegister(
        Request $request,
        logHelper $logHelper,
        EntityManagerInterface $em,
        DatesRepository $datesRepository,
        reservationHelper $reservationHelper,
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $formLoginAuthenticator,
        RememberMeHandlerInterface $rememberMe,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        $locale = "es"
    ){
        if ( $request->isXmlHttpRequest() ) {
            $userType = new User();
            $form = $this->createForm(UserType::class, $userType);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
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
                $user->setRoles(["ROLE_USER"]);
                $em->persist($user);
                $em->flush();
                
                $userAuthenticator->authenticateUser(
                    $user,
                    $formLoginAuthenticator,
                    $request
                );
                $rememberMe->createRememberMeCookie($this->getUser());
                //$loggedUserCardHtml = $this->renderView('reservation/usercards/_card_logged_user.html.twig',['isInitialized'=>false]);
                return $this->json([
                    'code'=> 200,
                    //'message' => $loggedUserCardHtml,
                    'message'=>'',
                    'user' => $user,
                    'userId' => $user ? $user->getId() : '',
                    'isInitialized' => false
                    ], 
                    200,
                    [],
                    ['groups'=>'main']
                );
                
            }
            $violations = $validator->validate($userType);
            
            if (0 !== count($violations->findByCodes(UniqueEntity::NOT_UNIQUE_ERROR))) {
                
                $userRequestData= $request->request->get('user');
                $user = $userRepository->findBy(
                    [
                        'email' => $userRequestData['email']
                    ]
                );
                
                if (null != $user){
                    $swalHtml = $this->renderview(
                        'reservation/usercards/_swal_login.html.twig',
                                        [ 
                                            'email' => $userRequestData['email'] 
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
                        'email' => $userRequestData['email']
                    ],401);
                }
            }

            $registerFormHtml = $this->renderView('reservation/usercards/_card_register.html.twig',[
                'locale' => $locale,
                /* 'date' => $date, */
                'form' => $form->createView(),
                'isInitialized' => false
            ]);
            
            return $this->json([
                        'html' => $registerFormHtml
                        ], 
                        401,
                        [],
                        ['groups'=>'main']);
        } 
           
    }

     /**
     * @Route("/ajax/initialize/reservation/login/", 
     * options = { "expose" = true }, 
     * name="api_auth_login",
     * methods={"POST"})
     */
    public function initializeAsLogin(
        Request $request,
        TokenStorageInterface $tokenStorage,
        RememberMeHandlerInterface $rememberMe
        ) : Response
    {
        
        $user = $this->getUser();
       
        if ($user instanceof User ){
            // Create a response object so we can attach the Remember Me Cookie
            // to be sent back to the client.
            $response = $this->json([
                'username' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
                'icon'=> 'success',
                'correctLogin' => 'Has entrado correctamente'
            ], 200,[],['groups'=>'main']);

            $rememberMe->createRememberMeCookie($this->getUser());

            return $response;
        }

        return $this->json([
            'message' => "Login failed",
            'icon' => 'error',
            'checkPassword' => "Comprueba tu contraseña"
        ], 401);
        
    }

    /**
     * @Route("/ajax/registered/", name="ajax_registered")
     */
    public function ajaxRegistered() :Response
    {
        return new response(true);
    }

    /**
     * @Route("/ajax/reservation/initialize/{_locale}", 
     * options = { "expose" = true }, 
     * name="initialize_reservation")
     * @Route({
     *      "en": "/ajax/reservation/initialize/{_locale}",
     *      "es": "/ajax/reservation/initialize/{_locale}",
     *      "fr": "/ajax/reservation/initialize/{_locale}"
     *      }, 
     * options = { "expose" = true }, 
     * name="initialize_reservation")
     */
    public function initializeReservation(
        Request $request,
        DatesRepository $datesRepository,
        ReservationRepository $reservationRepository,
        reservationHelper $reservationHelper,
        logHelper $logHelper,
        LangRepository $langRepository,
        TravelRepository $travelRepository,
        UserRepository $userRepository,
        InvoiceHelper $invoiceHelper,
        TravelTranslationRepository $travelTranslationRepository,
        CodespromoRepository $codespromoRepository,
        ReservationManager $reservationManager,
        string $_locale = null,
        string $locale = "es" ): Response {
            $locale = $_locale ? $_locale : $locale;
            $renderArray = [];
            $requestData = [
                'dateId' => $request->request->get('dateId'),
                'nbPilotes' => $request->request->get('nbPilotes'),
                'nbAccomp' => $request->request->get('nbAccomp'),
                'options' => $request->request->get('options'),
                'userId' => $request->request->get('user')
            ];
            
            
            $date = $datesRepository->find($requestData['dateId']);
            $user = $this->getUser();
            $isReservedObject = $reservationRepository->findOneBy([
                'user' => $user,
                'date'=> $date
            ]);
            if (null !== $isReservedObject) {
                $url = $this->generateUrl('frontend_user_reservation',[
                    'reservation' => $isReservedObject->getId(),
                    '_locale' => $locale
                ]);
                return $this->json([
                    'isReserved' => true,
                    'message' => $this->translator->trans('Esta reserva ya ha sido realizada, acude a tu espacio de usuario para continuar con tu proceso de reserva. <a href="'.$url.'">Tu reserva</a>'),
                    'user' => $user,
                    'userId' => $user ? $user->getId() : '',
                    'reservationId' => $isReservedObject->getId()

                ], 403, [], ['groups'=>'main']);
            } 
            $reservation = $reservationManager->sendReservation($requestData, $date, $user, $request->getLocale());
            
            /* $invoiceHelper->makeInvoice($reservation, '', [
                'name' => $user->getPrenom(). ' '.$user->getNom(),
                'address' => $user->getAddress(),
                'nif' => $user->getIdCard(),
                'postalcode' => $user->getPostcode(),
                'city' => $user->getCity(),
                'country' => $user->getCountry()
            ]); */
            
            
            //LOG INITIALISATION
            //$logHelper->logReservationInitialize($date, $reservation);
        
            // GET TRANSLATION FOR TRAVEL $travelTranslation
            $lang = $langRepository->findOneBy(['iso_code' => $locale]);
            $travelTranslation = $travelTranslationRepository->findOneBy([
                'lang' => $lang,
                'travel' => $reservation->getDate()->getTravel()
            ]);
            //CREATE RESERVATION OPTIONS ARRAY 
            $reservationOptions = $reservationHelper->getReservationOptions($reservation, $lang);
            
            $codepromo = $codespromoRepository
                            ->findOneBy([
                                'email' => $user->getEmail()
                            ]);
            if(null == $codepromo) {
                $codepromo = $codespromoRepository
                                ->findOneBy([
                                    'user' => $user
                                ]);
            }
            $swalHtml = $this->renderView('reservation/_swal_confirmation_message.html.twig', 
                                                [
                                                    'codepromo' => $codepromo,
                                                    '_locale' => $locale 
                                                ]);
            $renderHtml = $this->renderView('reservation/usercards/_card_logged_user.html.twig',
                                                [
                                                    'isInitialized'=>true,
                                                    'reservationOptions' =>$reservationOptions,
                                                    'reservation'=>$reservation]);
            
            $renderArray = [
                'code'=> 200,
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
                'locale' => $locale
            ];
            if($codepromo !== null) 
            {
                $renderArray = array_merge($renderArray, 
                    [
                        'codepromo' => $codepromo->getCode(),
                        'codepromoId' => $codepromo->getId(),
                        'codepromoMontant'=> $codepromo->getMontant(),
                        'codepromoPourcentage'=> $codepromo->getPourcentage(),
                        'codepromoDebut' => $codepromo->getDebut(),
                        'codepromoFin' => $codepromo->getFin()
                    ]
                );
            }
            
            return $this->json($renderArray, 200,[],['groups'=>'main']);
    }

    /**
     * @Route("/ajax/load-user-switch",
     *  options = { "expose" = true },
     *  name="ajax_load_user_switch")
     */
    public function ajaxLoadUserSwitch(Request $request): Response{
        return $this->render('shared/_user_switch.html.twig');
    }

    /**
     * @Route("/ajax/login-result",
     * options = { "expose" = true },
     * name="ajax_login_result",
     * methods={"POST"})
     */
    public function ajaxLoginResult(
        Request $request,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $em
        ): Response {

        $user = $this->getUser();
        $reservationId = $request->request->get('reservationId');
        if (null !== $reservationId) {
            //ASSIGN USER TO RESERVATION
            $reservation = $reservationRepository->find($reservationId);

            $reservation->setUser($user);
            $reservation->setStatus('assigned');
            $em->persist($reservation);
            $em->flush();
        }

        return $this->render('reservation/_login_result_card.html.twig', [
            'user' => $user,
            'reservationId' => $reservationId
        ]);
    }

    /**
     * @Route("/ajax/assign/codepromo/{reservation}/{codepromo}",
     * options={"expose":true },
     * methods={"GET"},
     * name="assign-codepromo" )
     */

    public function ajaxAssignCodepromo(
        Request $request,
        Codespromo $codepromo,
        Reservation $reservation,
        EntityManagerInterface $em )
        {
            $reservation->addCodespromo($codepromo);
            $em->persist($reservation);
            $em->flush();            
            return new Response('addes codes promo');
        }

    /**
     * @Route("/ajax/invoice/{invoice}", 
     * options = { "expose" = true },
     * name="ajax_invoice")
     */
    public function ajaxInvoice(
        Request $request, 
        Invoices $invoice
        ) :Response
        {
            
        $formInvoice = $this->createForm(InvoicesType::class, $invoice);
        $html = $this->renderView('user/partials/_card_reservation_invoice.html.twig',[
            'invoice' => $invoice,
            'formInvoice'=>$formInvoice->createView(),
        ]);
        return $this->json([
                'title' => $this->translator->trans("Completar datos de facturación"),
                'html' => $html
            ], 200, [], ['group'=>'main']);
        }

    /**
    * @Route("/ajax/save-invoice/{invoice}", 
    * options = { "expose" = true },
    * name="ajax_save_invoice")
    */
    public function ajaxSaveInvoice(
            Request $request,
            Invoices $invoice,
            EntityManagerInterface $em,
            pdfHelper $pdfHelper,
            UploadHelper $uploadHelper,
            Mailer $mailer,
            InvoicesRepository $invoicesRepository,
            invoiceHelper $invoiceHelper )
        {

        $customerData = $request->request->all(); 
        $invoiceStatus = $invoiceHelper->replaceInvoice($invoice, $customerData);
        
        //SEND BY EMAIL
        /* if ($invoiceStatus['new'] == true ){
            $mailer->sendInvoiceToCustomer( 
                $invoiceStatus['pdf'], 
                $invoice->getReservation(),
                $invoiceStatus['number']);
            return new Response( "The pdf has been saved" );
        } else {
            return new Response( "The invoice has already been created");
        } */

        $html = $this->renderView('user/_row_user_invoices.html.twig',[
            'invoice' => $invoice
        ]);
        return $this->json([
            'status' => 200,
            'message' => 'The pdf has been saved',
            'html' => $html
        ], 200);
        return new Response( "The pdf has been saved" );
    }

    /**
    * @Route("/ajax/add-travellers/{reservation}", 
    * options = { "expose" = true },
    * name="ajax-add-travellers")
    */

    public function ajaxAddTravellers(
        Request $request,
        Reservation $reservation,
        TravellersRepository $travellersRepository
    )
    {
        

        foreach($travellersArray['traveller'] as $data){
            $traveller = new Travellers;
            $now = new \DateTime();
            $traveller->setPrenom($data['prenom']);
            $traveller->setNom($data['nom']);
            $traveller->setEmail($data['email']);
            $traveller->setTelephone($data['telephone']);
            $traveller->setPosition($data['position']);
            $traveller->setUser($this->getUser());
            $traveller->setDateAjout($now);
            $traveller->addReservation($reservation);
            $this->em->persist($traveller);
        }
        $this->em->flush();

        $travellersTableHtml = $this->renderView('reservation/_travellers_table.html.twig',[
            'travellers' => $reservation->getTravellers()
        ]);
        return $this->json([
            'travellersTableHtml' => $travellersTableHtml,
            'travellersByReservation'=>$reservation->getTravellers()
            ], 200, [], ['groups'=>'main']);
    }

    /**
    * @Route("/ajax/add/user",
    * options = { "expose" = true },
    * name="ajax-assign-user"
    * )
    */

    public function ajaxAssignUser(){
        $user = $this->getUser();
        return $this->json([
            'user' => [
                'nom' => $user->getNom(),
                'prenom'=> $user->getPrenom(),
                'email' => $user->getEmail(),
                'telephone' => $user->getTelephone()
                ]
            ],
            200,
            [],
            ['groups' => 'main']
        );
    }

    /**
    * @Route("/ajax/cancel/reservation",
    * options = {"expose" = true},
    * methods = {"POST"},
    *  name = "ajax-cancel-reservation")
    */

    public function ajaxCancelReservation(
            Request $request, 
            ReservationRepository $reservationRepository,
            EntityManagerInterface $em,
            InvoiceHelper $invoiceHelper ){
        $reservation = $reservationRepository
                        ->find($request->request->get('reservationId'));
        $reservation->setStatus('cancelled');
        $em->flush();
        $customerData = [];
        $user = $this->getUser();
        $invoiceHelper->makeInvoice($reservation, 'cancelled', [
            'name' => $user->getPrenom(). ' '.$user->getNom(),
            'address' => $user->getAdress(),
            'nif' => $user->getIdCard(),
            'postalcode' => $user->getPostalCode(),
            'city' => $user->getCity(),
            'country' => $user->getCountry()
        ], $request->getLocale());
        $invoiceHelper->createCancelationInvoice($reservation);



        return new Response('Cancel Reservation');
    }

    /**
    * @Route("/ajax/reactivate/reservation",
    * options = {"expose" = true},
    * methods = {"POST"},
    *  name = "ajax-reactivate-reservation")
    */

    public function ajaxReactivateReservation(
        Request $request, 
        ReservationRepository $reservationRepository,
        EntityManagerInterface $em ){
    $reservation = $reservationRepository->find($request->request->get('reservationId'));
    $reservation->setStatus('initialized');
    $em->flush();
    return new Response('Reactivate Reservation');
}

    /**
    * @Route("ajax/reservation/setStatus/{reservation}",
    * options = {"expose" = true},
    * methods = {"POST"},
    * name="ajax_reservation_set_status")
    */

    public function ajaxReservationSetStatus(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        invoiceHelper $invoiceHelper
    ) {
        $previousStatus = $reservation->getStatus();

        $newStatus = $request->request->get('status');
        $user = $this->getUser();
        $customerData = [
            'name' => $user->getPrenom(). ' '.$user->getNom(),
            'address' => $user->getAddress(),
            'nif' => $user->getIdCard(),
            'postalcode' => $user->getPostcode(),
            'city' => $user->getCity(),
            'country' => $user->getCountry()
        ];
       
        if ($newStatus != $previousStatus) {
            $invoiceHelper->makeInvoice($reservation, $newStatus, $customerData, $request->getLocale());
            $reservation->setStatus($newStatus);
            $em->persist($reservation);
            $em->flush();

            if($newStatus == 'initialized') {
                return $this->json([
                    'status' => 200,
                    'newStatus' => $newStatus,
                    'previousStatus' => $previousStatus,
                    'message' => $translator->trans('Esta reserva se ha cambiado a inicializada'),
                    'label' => $translator->trans('Cancelar esta reserva')
                ],200);
            }
            return $this->json([
                'status' => 200,
                'newStatus' => $newStatus,
                'previousStatus' => $previousStatus,
                'message' => $translator->trans('Esta reserva se ha cambiado a cancelada'),
                'label' => $translator->trans('Reinicializar esta reserva')
            ],200);

        }

        return $this->json([
                'status' => 401,
                'newStatus' => $newStatus,
                'previousStatus' => $previousStatus,
                'message' => $translator->trans('no ha de ser cambiada')
            ],401);

        /* return $this->json([
        'status' => 200,
        'response' => $translator->trans('Esta reserva se ha cambiado a cancelada')
        ],200); */
    }    

    /**
     * @Route(
     * "ajax/update/changes/{reservation}",
     * options = {"expose" = true},
     * name="ajax-update-changes", 
     * methods={"POST"})
     * 
     */

     public function ajaxUpdateChanges(
        Request $request,
        Reservation $reservation,
        ReservationRepository $reservationRepository,
        invoiceHelper $invoiceHelper,
        ReservationOptionsRepository $reservationOptionsRepository
     )
     {
        $requestData = [
            'nbPilotes' => $request->request->get('nbPilotes'),
            'nbAccomp' => $request->request->get('nbAccomp'),
            'options' => json_decode($request->request->get('options'))
        ];
        
        $reservationRepository->find($reservation->getId());

        $user = $this->getUser();
        $customerData = [
            'name' => $user->getPrenom(). ' '.$user->getNom(),
            'address' => $user->getAddress(),
            'nif' => $user->getIdCard(),
            'postalcode' => $user->getPostcode(),
            'city' => $user->getCity(),
            'country' => $user->getCountry()
        ];
        
        $invoiceHelper->makeInvoice($reservation, 'cancelled', $customerData, $request->getLocale());
        
        $reservation->setNbpilotes($requestData['nbPilotes']);
        $reservation->setNbAccomp($requestData['nbAccomp']);
        
        foreach( $reservation->getReservationOptions() as $reservationOption){
            $reservationOptionObject = $reservationOptionsRepository->find( $reservationOption->getId() );
           foreach( $requestData['options'] as $changedOption ) {
               //dd($changedOption);
               if(isset($changedOption->ReservationOptionId) && $reservationOptionObject->getId() == $changedOption->ReservationOptionId) {
                    $reservationOptionObject->setAmmount($changedOption->ammount);
               }
           }
        }
           
        $invoiceHelper->makeInvoice($reservation, 'initialized', $customerData , $request->getLocale());
        return new Response('Done');

     }

     /**
      * @Route("ajax/reservation/applyCodePromo/{codepromo}", 
      * options = {"expose" = true},
      * name="apply-code-promo", methods={"POST"})
      */
     public function applyCodePromo(
         Request $request,
         Codespromo $codepromo,
         ReservationRepository $reservationRepository,
         reservationHelper $reservationHelper,
         EntityManagerInterface $em ){
        $reservationId = $request->request->get('reservation');
        $reservation = $reservationRepository->find($reservationId);
        $reservation->addCodespromo($codepromo);
        $em->persist($reservation);
        
        $totalAmmount = $reservationHelper->getReservationAmmountBeforeDiscount($reservation);
        $now = new \DateTime();
        if($codepromo->getType() == 'uses'){
            $codepromo->setNombre($codepromo->getNombre()-1);
            if($codepromo->getNombre() == 0 ){
                $codepromo->setStatut('Inactif');
            }
            $em->persist($codepromo);
        } else if ($codepromo->getType() == 'period'){
            if($codepromo->getDebut() <= $now && $now <= $codepromo->getFin()){
                dd('valid period');
            }
        }
        
        $em->flush();
        if ($codepromo->getMontant() != null && $codepromo->getPourcentage() == null){
            $discountedAmmount = $totalAmmount - $codepromo->getMontant();
        }
        if ($codepromo->getPourcentage() != null && $codepromo->getMontant() == null){
            $discountedAmmount = $totalAmmount * ( 1 - $codepromo->getPourcentage() / 100 );
        }


        return $this->json( [
            'status' => 200,
            'message' => 'hello',
            'codeTitle' => $codepromo->getCode(),
            'codeMontant' => $codepromo->getMontant(),
            'codePourcentage' => $codepromo->getPourcentage(),
            'totalAmmount' => $totalAmmount,
            'discountedAmmount' => $discountedAmmount
        ] , 200);
     }

     /**
      * @Route("ajax/update-calculator/{_locale}",
      * methods={"POST","GET"}, 
      * name="update-calculator", 
      * options={ "expose" = true })
      * @Route({
      *      "en": "ajax/update-calculator/{_locale}",
      *      "es": "ajax/actualizar-calculadora/{_locale}",
      *      "fr": "ajax/actualiser-calculatrice/{_locale}"
      *      },
      * methods={"POST","GET"}, 
      * name="update-calculator", 
      * options={ "expose" = true })
      */

      public function updateCalculator(
        Request $request,
        ReservationRepository $reservationRepository,
        DatesRepository $datesRepository,
        string $locale = "es",
        $_locale = null
      ) : Response
      {
        //dump($_locale);
        $locale = $_locale? $_locale : $locale;
        dump($locale);
        $data = $request->request->all();
        /* $request->setLocale($data['locale']);
        dump($request->getLocale($locale));
        dump($data); */
        $date = $datesRepository->find($data['dateId']);
        $renderArray = [
            'date' => $date,
            'locale'=> $_locale,
            '_locale'=> $locale
        ];
        dump($renderArray);
        $renderArray = array_merge($renderArray,$data);
        dump($renderArray);
        //$options = $reservationHelper->getReservationOptions($reservation, $this->langRepository->findBy(['iso_code'=>$request->getLocale()]));
        if(isset ($data['reservation'])){
            $reservation = $reservationRepository->find($data['reservation']);
            $renderArray['reservation'] = $reservation;
            dump($reservation->getId());
            dump($reservation->getCodespromos());
        }
        
        $renderArray['options'] = (isset( $data['options'] ))? $data['options'] : "";
        $renderArray['reservationOptions'] = (isset( $data['options'] ))? $data['options'] : "";
        $renderArray['optionsJson'] = (isset( $data['options'] ))? json_encode($data['options']) : "";
        $renderArray['isInitialized'] = (isset( $data['isInitialized'] ))? $data['isInitialized'] : "";
        $renderArray['userEdit'] = (isset( $data['userEdit'] ))? $data['userEdit'] : "";
       
        dump($renderArray);
        return $this->render('reservation/_wrapper_calculator_logged_user.html.twig',$renderArray);
      }
}
