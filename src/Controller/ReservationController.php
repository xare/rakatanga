<?php

namespace App\Controller;

use Adamski\Symfony\PhoneNumberBundle\Model\PhoneNumber;
use App\Entity\Invoices;
use App\Entity\Lang;
use App\Entity\OptionsTranslations;
use App\Entity\Payments;
use App\Entity\Reservation;
use App\Entity\ReservationOptions;
use App\Entity\TravelTranslation;
use App\Entity\User;
use App\Entity\Travellers;
use App\Form\InvoicesType;
use App\Form\UserType;
use App\Repository\CategoryTranslationRepository;
use App\Repository\DatesRepository;
use App\Repository\InvoicesRepository;
use App\Repository\LangRepository;
use App\Repository\OptionsRepository;
use App\Repository\PaymentsRepository;
use App\Repository\ReservationOptionsRepository;
use App\Repository\ReservationRepository;
use App\Repository\TravellersRepository;
use App\Repository\TravelRepository;
use App\Repository\TravelTranslationRepository;
use App\Repository\UserRepository;
use App\Service\breadcrumbsHelper;
use App\Service\localizationHelper;
use App\Service\logHelper;
use App\Service\Mailer;
use App\Service\pdfHelper;
use App\Service\reservationHelper;
use App\Service\slugifyHelper;
use App\Service\UploadHelper;
use App\Service\invoiceHelper;
use App\Service\reservationDataHelper;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;
use Stripe\BillingPortal\Session as BillingPortalSession;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Http\Authenticator\JsonLoginAuthenticator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReservationController extends AbstractController
{
    private $em;
    private $translator; 
    private $breadcrumbs;
    private $localizationHelper;
    private $slugifyHelper;
    private $reservationHelper;
    private $breadcrumbsHelper;

    
    public function __construct(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Breadcrumbs $breadcrumbs,
        localizationHelper $localizationHelper,
        slugifyHelper $slugifyHelper,
        reservationHelper $reservationHelper,
        breadcrumbsHelper $breadcrumbsHelper,
        private string $stripePublicKey
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->breadcrumbs = $breadcrumbs;
        $this->localizationHelper = $localizationHelper;
        $this->slugifyHelper = $slugifyHelper;
        $this->reservationHelper = $reservationHelper;
        $this->breadcrumbsHelper = $breadcrumbsHelper;
    }
    #[Route(path: '/trips/{category}/{travel}/reservation/{date}/', name: 'reservation', priority: 10)]
     #[Route(path: ['en' => '{_locale}/trips/{category}/{travel}/reservation/{date}/', 'es' => '{_locale}/trips/{category}/{travel}/reserva/{date}/', 'fr' => '{_locale}/trips/{category}/{travel}/reservation/{date}/'], name: 'reservation', priority: 10)]
     public function index(
                        Request $request,
                        $_locale = null,
                        TravelTranslationRepository $travelTranslationRepository,
                        CategoryTranslationRepository $categoryTranslationRepository,
                        DatesRepository $datesRepository,
                        LangRepository $langRepository,
                        $locale = "es"

     ){
        //ROUTE ELEMENTS 
        $locale = $locale ? $_locale : $locale;

        $travelTranslation = $travelTranslationRepository->findOneBy([
            'url' => $request->attributes->get('travel')
        ]);

        $categoryTranslation = $categoryTranslationRepository->findOneBy([
            'slug' => $request->attributes->get('category')
        ]);

        $dateString = $request->attributes->get('date');

        $dateString = substr($dateString, 0, 4) ."-". 
                        substr($dateString, 4, 2) ."-".
                        substr($dateString, 6, 2);

        $date = $datesRepository->findDate($dateString, $travelTranslation->getTravel()); 
        //END ROUTE RESOLVER

        // SWITCH LOCALE LOADER
        $otherLangsArray = $langRepository->findOthers($locale);
        
        $i = 0;
        $urlArray = [];
        /* $dateUrl = $date[0]->getDebut()->format('Ymd');
        if (is_array($date)){
                $dateUrl = $dateUrl."_".$date[0]->getFin()->format('Ymd');
        } */
        foreach($otherLangsArray as $otherLangArray){
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $otherCategoryTranslation = $categoryTranslationRepository->findOneBy(
                [   
                    'category'=>$categoryTranslation->getCategory(),
                    'lang' => $otherLangArray
                ]);
            $urlArray[$i]['category'] = $otherCategoryTranslation->getSlug();
            $otherTravelTranslation = $travelTranslationRepository->findOneBy(
                            [
                                'travel' => $travelTranslation->getTravel(),
                                'lang'=>$otherLangArray
                            ]);
            $urlArray[$i]['travel'] = $otherTravelTranslation->getUrl();
            $urlArray[$i]['date'] = $date->getDebut()->format('Ymd');
            $i++;
        }
        // END SWITCH LOCALE LOADER

        // BREADCRUMBS //
        $this->breadcrumbsHelper->reservationBreadcrumbs($locale, $date);
         // END BREADCRUMBS //

        $renderArray = [
            'locale' => $locale,
            'langs' => $urlArray,
            'date' => $date,
            'form' => ''
        ];
        
        $user = $this->getUser();
        $renderArray['user'] = $user;
        if ( $user == null){
            $user = new User;
            $form = $this->createForm(UserType::class, $user);
            $renderArray['form'] = $form->createView();
        }

        $isReserved = $this->reservationHelper->_isReserved($user, $date);
        $renderArray['isInitialized'] = false;
        if($isReserved !=  null){
            $renderArray['isInitialized'] = true;
            $url = $this->generateUrl(
                'frontend_user_reservation',
                [ 'reservation' => $isReserved->getId() ] );
            $introMessage = $this->translator->trans(
                'Ya has comenzado tu reserva para este viaje').".";
            $linkMessage = $this->translator->trans('Ver reserva');
            $link = sprintf('<a href="%s">'.$linkMessage.'</a>', $url);
            //$message = $introMessage . ' '. $link; 
            $this->addFlash(
                'error', 
                $link
                );
        }
        
         //RENDER TEMPLATE
        
         return $this->render('reservation/index.html.twig', $renderArray);

     }

    
    #[Route(path: '/reservation/{reservation}/payment', priority: 6, name: 'reservation_payment', requirements: ['reservation' => '\d+'], methods: ['POST', 'GET'], options: ['expose' => true])]
    #[Route(path: ['en' => '{_locale}/reservation/{reservation}/payment', 'es' => '{_locale}/reservation/{reservation}/payment', 'fr' => '{_locale}/reservation/{reservation}/payment'], priority: 10, name: 'reservation_payment', requirements: ['reservation' => '\d+'], methods: ['POST', 'GET'], options: ['expose' => true])]
    public function reservationPayment(
        Request $request,
        Reservation $reservation,
        AuthenticationUtils $authenticationUtils,
        LangRepository $langRepository,
        reservationHelper $reservationHelper,
        reservationDataHelper $reservationDataHelper,
        $stripeSK,
        FormFactoryInterface $formFactory,
        string $_locale = null,
        string $locale = 'es' )
    {
        
        $locale = $_locale? $_locale : $locale;
        $reservationData = $request->request->all();
        
        if ( isset($reservationData['userEdit']) && $reservationData['userEdit'] == true ) 
            $reservation = $this->reservationHelper->updateReservation($reservation, $reservationData, $locale);
        //LANG MENU
        $otherLangsArray = $langRepository->findOthers($locale);
        $urlArray = [];
        $i=0;
        foreach($otherLangsArray as $otherLangArray){
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $urlArray[$i]['reservation'] = $reservation->getId();
            $i++;
        }
        //END LANG MENU
        // BREADCRUMBS //
        $this->breadcrumbsHelper->reservationPaymentBreadcrumbs($locale, $reservation);
         // END BREADCRUMBS //

        $date = $reservation->getDate();
        $travelTitle = $date->getTravel()->getMainTitle();
        
        $error = $authenticationUtils->getLastAuthenticationError();

        $totalAmmount = $reservationDataHelper->getReservationDueAmmount($reservation);
        
        $ammount = $request->request->get('ammount') ? $request->request->get('ammount') : $totalAmmount;
        
        $form = $formFactory
                    ->createNamedBuilder(
                        'payment-form',
                        FormType::class,
                        null,
                        array('csrf_protection' => false))
                ->add('paymentMethod', ChoiceType::class, [
                        'choices' => [
                            $this->translator->trans('Stripe') => 'stripe',
                            $this->translator->trans('Stripe 500€') => 'stripe500',
                            $this->translator->trans('Transferencia Bancaria') => 'bankTransfer'
                        ],
                        'expanded' => true,
                        'multiple' => false
                    ])
                    ->add('submit', SubmitType::class, [
                        'attr' => ['class' => 'btn btn-primary btn-lg']
                    ])
                    ->add('submit2', SubmitType::class, [
                        'attr' => ['class' => 'btn btn-primary btn-lg']
                    ])
                    ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();
                if (
                    $formData['paymentMethod'] == "stripe" || 
                    $formData['paymentMethod'] =="stripe500"){
                        if($formData['paymentMethod'] =="stripe500")
                            $ammount = 500;
                    
                    \Stripe\Stripe::setApiKey($stripeSK);

                    $session = CheckoutSession::create([
                        'payment_method_types' => ['card'],
                        'mode' => 'payment',
                        'locale' => $locale,
                        'line_items'           => [
                            [
                                'price_data' => [
                                    'currency'     => 'eur',
                                    'product_data' => [
                                        'name' => $travelTitle,
                                        'metadata' => [
                                            'paymentType' =>$formData['paymentMethod']
                                        ]
                                    ],
                                    'unit_amount'  => $ammount * 100,
                                ],
                                'quantity'   => 1,
                            ]
                        ],
                        
                        'success_url'   => $this->generateUrl(
                                                'success_url', 
                                                [
                                                    'reservation' => $reservation->getId(),
                                                    'ammount' =>$ammount,
                                                    '_locale'=>$locale,
                                                    //'session_id'=>'{CHECKOUT_SESSION_ID}'
                                                ], 
                                                UrlGeneratorInterface::ABSOLUTE_URL).'&session_id={CHECKOUT_SESSION_ID}',
                        'cancel_url'    => $this->generateUrl(
                                                'cancel_url', 
                                                [
                                                    'reservation' => $reservation->getId(),
                                                    '_locale'=>$locale
                                                ],
                                                UrlGeneratorInterface::ABSOLUTE_URL),
                    ]);
                    return $this->redirect($session->url, \Symfony\Component\HttpFoundation\Response::HTTP_SEE_OTHER);
                } else {
                    return $this->render('reservation/reservationPaymentBank.html.twig',[
                        'langs' => $urlArray
                    ]);
                }
            }
        }
        $lang = $langRepository->findOneBy([
                'iso_code' => $locale
            ]);
        $optionsArray = $reservationHelper->getReservationOptions($reservation, $lang);

        //LOG PAYMENT
        /**
         * @var User $user
         */
        $user = $this->getUser();
        /* $logHelper->logThis(
            'Reserva Creada', 
            "{$user->getPrenom()} {$user->getNom()}[{$user->getEmail()}] ha creado una reserva para el viaje
            {$reservation->getDate()->getTravel()->getMainTitle()} en
            {$reservation->getDate()->getDebut()->format('d/m/Y')} with reference
            {$reservation->getId()} " . strtoupper(substr($reservation->getDate()->getTravel()->getMainTitle(),0,3))."",
            [],
            'reservation'); */

        // SEND EMAIL
        /* $mailer->sendReservationToUs($reservation,$locale);
        $mailer->sendReservationToSender($reservation); */

        return $this->render('reservation/reservationPayment.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'error' => $error,
            'date' => $date,
            'discount' => $reservationDataHelper->getDiscount($reservation),
            'reservation' => $reservation,
            'form' => $form->createView(),
            'ammount' => $ammount,
            'options' => $optionsArray,
            'optionsJson' => json_encode($optionsArray),
            'stripe_public_key' => $this->stripePublicKey,
        ]);
    }

    #[Route(path: '/{_locale}/reservation/{reservation}/payment/success-url', name: 'success_url', requirements: ['reservation' => '\d+'])]
    public function successUrl(
        Request $request,
        Reservation $reservation,
        string $_locale = null,
        logHelper $logHelper,
        Mailer $mailer,
        LangRepository $langRepository,
        EntityManagerInterface $em,
        reservationHelper $reservationHelper,
        PaymentsRepository $paymentsRepository,
        $stripeSK,
        $locale = 'es'): Response
    {
        $locale = $_locale?$_locale:$locale;
        
        

        // LANGS ARRAY
        $otherLangsArray = $langRepository->findOthers($locale);
        $urlArray = [];
        $i=0;
        foreach($otherLangsArray as $otherLangArray){
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $urlArray[$i]['reservation'] = $reservation->getId();
            $i++;
        }
        // END LANGS ARRAY
        \Stripe\Stripe::setApiKey($stripeSK);
        $session = \Stripe\Checkout\Session::retrieve($request->query->get('session_id'));

        if (count($paymentsRepository->findBy(['stripeId'=>$request->query->get('session_id')])) === 0){
            $payment = new Payments;
            $paidAmmount = $request->query->get('ammount');
            $payment->setReservation($reservation);
            $payment->setStripeId($request->query->get('session_id'));
            $payment->setAmmount($paidAmmount);
            $em->persist($payment);
             //LOG PAYMENT
            /**
             * @var User $user
             */
            $user = $this->getUser();
            $logHelper->logThis(
                'Payment Received', 
                "{$user->getPrenom()} {$user->getNom()}[{$user->getEmail()}] has made a payment for
                {$reservation->getDate()->getTravel()->getMainTitle()} on {$reservation->getDate()->getDebut()->format('d/m/Y')}",
                [],
                'reservation');

            // SEND EMAIL
            $mailer->sendReservationPaymentSuccessToUs( $reservation, $locale );
            $mailer->sendReservationPaymentSuccessToSender( $reservation );
        }
        $reservation->setStatus('payment-success');
        if($reservation->getCodespromo() != null ) {
           /*  foreach ($reservation->getCodespromo() as $codepromo)
            {
                dump($codepromo); */
                $codepromo = $reservation->getCodespromo();
                if($codepromo->getNombre() > 0) {
                    $codepromo->setNombre($codepromo->getNombre() + 1 );                    
                    $em->persist($codepromo);
                }
            /* } */
        }
        $em->persist($reservation);
        $em->flush();

       
        

        return $this->render('reservation/reservationPaymentSuccess.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'reservation'=> $reservation,
        ]);
    }

    #[Route(path: '{_locale}/reservation/{reservation}/payment/cancel-url', name: 'cancel_url')]
    #[Route(path: ['en' => '{_locale}/reservation/{reservation}/payment/cancel-url', 'es' => '{_locale}/reservation/{reservation}/payment/cancel-url', 'fr' => '{_locale}/reservation/{reservation}/payment/cancel-url'], priority: 10, name: 'cancel_url', requirements: ['reservation' => '\d+'])]
    public function cancelUrl(
                        Reservation $reservation,
                        LangRepository $langRepository,
                        $locale= "es", 
                        $_locale = null): Response
    {
        $locale = $_locale?$_locale:$locale;
        $otherLangsArray = $langRepository->findOthers($locale);
        $urlArray = [];
        $i=0;
        foreach($otherLangsArray as $otherLangArray){
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $urlArray[$i]['reservation'] = $reservation->getId();
            $i++;
        }
        return $this->render('reservation/reservationPaymentCancel.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray
        ]);
    }

    #[Route(path: '/reservation/{reservation}/options', options: ['expose' => true], name: 'get_previous_options')]
    public function getPreviousOptions(
        Request $request,
        Reservation $reservation,
        LangRepository $langRepository,
        reservationHelper $reservationHelper)
        {
            $lang = $langRepository->findOneBy([
                'iso_code' => $request->getLocale()
            ]);
        $options = $reservationHelper->getReservationOptions($reservation, $lang);
        
        return $this->json([
            'options' => $options
        ], 200,[],['groups'=>'main']);

    }
    
}