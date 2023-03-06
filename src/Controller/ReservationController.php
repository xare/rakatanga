<?php

namespace App\Controller;

use App\Entity\Payments;
use App\Entity\Reservation;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\CategoryTranslationRepository;
use App\Repository\DatesRepository;
use App\Repository\LangRepository;
use App\Repository\PaymentsRepository;
use App\Repository\TravelTranslationRepository;
use App\Service\breadcrumbsHelper;
use App\Service\languageMenuHelper;
use App\Service\localizationHelper;
use App\Service\logHelper;
use App\Service\Mailer;
use App\Service\paymentHelper;
use App\Service\reservationDataHelper;
use App\Service\reservationHelper;
use App\Service\slugifyHelper;
use App\Service\travellersHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class ReservationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TravelTranslationRepository $travelTranslationRepository,
        private CategoryTranslationRepository $categoryTranslationRepository,
        private DatesRepository $datesRepository,
        private LangRepository $langRepository,
        private PaymentsRepository $paymentsRepository,
        private TranslatorInterface $translator,
        private Breadcrumbs $breadcrumbs,
        private localizationHelper $localizationHelper,
        private slugifyHelper $slugifyHelper,
        private reservationHelper $reservationHelper,
        private breadcrumbsHelper $breadcrumbsHelper,
        private paymentHelper $paymentHelper,
        private logHelper $logHelper,
        private languageMenuHelper $languageMenuHelper,
        private reservationDataHelper $reservationDataHelper,
        private travellersHelper $travellersHelper,
        private string $stripePublicKey,
        private string $stripeSecretKey
    ) {
    }

    #[Route(
        path: '/trips/{category}/{travel}/reservation/{date}/',
        name: 'reservation',
        priority: 10)]
     #[Route(
        path: [
            'en' => '{_locale}/trips/{category}/{travel}/reservation/{date}/',
            'es' => '{_locale}/trips/{category}/{travel}/reserva/{date}/',
            'fr' => '{_locale}/trips/{category}/{travel}/reservation/{date}/'],
        name: 'reservation',
        priority: 10)]
     public function index(
                        Request $request,
                        string $_locale = null,
                        string $locale = 'es'
     ):Response {

         $locale = $locale ? $_locale : $locale;
         $travelTranslation = $this->travelTranslationRepository->findOneBy([
             'url' => $request->attributes->get('travel'),
         ]);
         $categoryTranslation = $this->categoryTranslationRepository->findOneBy([
             'slug' => $request->attributes->get('category'),
         ]);
         $dateString = $request->attributes->get('date');

         $dateString = substr($dateString, 0, 4).'-'.
                         substr($dateString, 4, 2).'-'.
                         substr($dateString, 6, 2);

         $date = $this->datesRepository->findDate($dateString, $travelTranslation->getTravel());

         $urlArray = $this->languageMenuHelper->reservationMenuLanguage(
            $locale,
            $travelTranslation,
            $categoryTranslation,
            $date);

         $this->breadcrumbsHelper->reservationBreadcrumbs($locale, $date);

         $renderArray = [
             'locale' => $locale,
             'langs' => $urlArray,
             'date' => $date,
             'form' => '',
         ];
         /**
          * @var $user
          */
         $user = $this->getUser();
         $renderArray['user'] = $user;
         if ($user == null) {
             $user = new User();
             $form = $this->createForm(UserType::class, $user);
             $renderArray['form'] = $form->createView();
         }

         $isReserved = $this->reservationHelper->_isReserved($user, $date);
         $renderArray['isInitialized'] = false;
         if ($isReserved != null) {
             $renderArray['isInitialized'] = true;
             $url = $this->generateUrl(
                 'frontend_user_reservation',
                 ['reservation' => $isReserved->getId()]);
             $introMessage = $this->translator->trans(
                 'Ya has comenzado tu reserva para este viaje').'.';
             $linkMessage = $this->translator->trans('Ver reserva');
             $link = sprintf('<a href="%s">'.$linkMessage.'</a>', $url);
             $this->addFlash(
                 'error',
                 $link
             );
         }

         return $this->render('reservation/index.html.twig', $renderArray);
     }

    #[Route(
        path: '{_locale}/reservation/{reservation}/payment',
        priority: 6,
        name: 'reservation_payment',
        requirements: ['reservation' => '\d+'],
        methods: ['POST', 'GET'],
        options: ['expose' => true])]
    #[Route(
        path: [
            'en' => '{_locale}/reservation/{reservation}/payment',
            'es' => '{_locale}/reserva/{reservation}/pago',
            'fr' => '{_locale}/reservation/{reservation}/paiement'],
        priority: 10,
        name: 'reservation_payment',
        requirements: ['reservation' => '\d+'],
        methods: ['POST', 'GET'],
        options: ['expose' => true])]
    public function reservationPayment(
        Request $request,
        Reservation $reservation,
        AuthenticationUtils $authenticationUtils,
        FormFactoryInterface $formFactory,
        string $_locale = null,
        string $locale = 'es'):Response
    {
        $locale = $_locale ? $_locale : $locale;
        $reservationData = $request->request->all();
        dump($reservation);
        if (
            isset($reservationData['userEdit'])
            && $reservationData['userEdit'] == true) {
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
                $locale);
        }
        // LANG MENU
        $urlArray = $this->languageMenuHelper->reservationPaymentMenuLanguage($locale, $reservation);

        $this->breadcrumbsHelper->reservationPaymentBreadcrumbs($locale, $reservation);

        $date = $reservation->getDate();
        $travelTitle = $date->getTravel()->getMainTitle();

        $error = $authenticationUtils->getLastAuthenticationError();

        $totalAmmount = $this->reservationDataHelper->getReservationDuePayment($reservation);

        $ammount = $request->request->get('ammount') ? $request->request->get('ammount') : $totalAmmount;

        $form = $formFactory
                    ->createNamedBuilder(
                        'payment-form',
                        FormType::class,
                        null,
                        ['csrf_protection' => false])
                ->add('paymentMethod', ChoiceType::class, [
                        'choices' => [
                            'Stripe' => 'stripe',
                            'Stripe 500€' => 'stripe500',
                            $this->translator->trans('Transferencia Bancaria') => 'bankTransfer',
                        ],
                        'expanded' => true,
                        'multiple' => false,
                    ])
                    ->add('submit', SubmitType::class, [
                        'attr' => ['class' => 'btn btn-primary btn-lg'],
                    ])
                    ->add('submit2', SubmitType::class, [
                        'attr' => ['class' => 'btn btn-primary btn-lg'],
                    ])
                    ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();
                if (
                    $formData['paymentMethod'] == 'stripe' ||
                    $formData['paymentMethod'] == 'stripe500') {
                    if ($formData['paymentMethod'] == 'stripe500') {
                        $ammount = 500;
                    }

                    $session = $this->paymentHelper->createStripeCheckout($reservation, $locale, $formData, $ammount);
                    return $this->redirect($session->url, \Symfony\Component\HttpFoundation\Response::HTTP_SEE_OTHER);
                } else {
                    return $this->render('reservation/reservationPaymentBank.html.twig', [
                        'locale'=>$locale,
                        'reservation'=> $reservation,
                        'langs' => $urlArray,
                    ]);
                }
            }
        }
        /* $lang = $this->langRepository->findOneBy([
                'iso_code' => $locale,
            ]); */
        $optionsArray = $this->reservationHelper->getReservationOptions($reservation, $locale);

        return $this->render('reservation/reservationPayment.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'error' => $error,
            'date' => $date,
            'discount' => $this->reservationDataHelper->getDiscount($reservation),
            'reservation' => $reservation,
            'form' => $form->createView(),
            'ammount' => $ammount,
            'options' => $optionsArray,
            'optionsJson' => json_encode($optionsArray),
            'stripe_public_key' => $this->stripePublicKey,
        ]);
    }

    #[Route(
        path: '/{_locale}/reservation/{reservation}/payment/success',
        name: 'success_url',
        requirements: ['reservation' => '\d+'])]
    #[Route(
        path: [
            'en' => '{_locale}/reservation/{reservation}/payment/success',
            'es' => '{_locale}/reserva/{reservation}/pago/realizado',
            'fr' => '{_locale}/reservation/{reservation}/paiement/success'],
        priority: 10,
        name: 'success_url',
        requirements: ['reservation' => '\d+'])]
    public function successUrl(
        Request $request,
        Reservation $reservation,
        Mailer $mailer,
        string $_locale = null,
        string $locale = 'es'):Response
    {
        $locale = $_locale ? $_locale : $locale;

        $urlArray = $this->languageMenuHelper->paymentSuccessReservationMenuLanguage($locale, $reservation);

        \Stripe\Stripe::setApiKey($this->stripeSecretKey);
        $session = \Stripe\Checkout\Session::retrieve($request->query->get('session_id'));

        if (count($this->paymentsRepository->findBy(['stripeId' => $request->query->get('session_id')])) === 0) {
            $payment = new Payments();
            $paidAmmount = $request->query->get('ammount');
            $payment->setReservation($reservation);
            $payment->setStripeId($request->query->get('session_id'));
            $payment->setAmmount($paidAmmount);
            $this->entityManager->persist($payment);
            /**
             * @var User $user
             */
            $user = $this->getUser();
            $this->logHelper->logThis(
                'Payment Received',
                "{$user->getPrenom()} {$user->getNom()}[{$user->getEmail()}] has made a payment for
                {$reservation->getDate()->getTravel()->getMainTitle()} on {$reservation->getDate()->getDebut()->format('d/m/Y')}",
                [],
                'reservation');

            // SEND EMAIL
            $mailer->sendReservationPaymentSuccessToUs($reservation, $locale);
            $mailer->sendReservationPaymentSuccessToSender($reservation);
        }
        $reservation->setStatus('payment-success');
        if ($reservation->getCodespromo() != null) {
            $codepromo = $reservation->getCodespromo();
            if ($codepromo->getNombre() > 0) {
                $codepromo->setNombre($codepromo->getNombre() + 1);
                $this->entityManager->persist($codepromo);
            }
        }
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $this->render('reservation/reservationPaymentSuccess.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'reservation' => $reservation,
        ]);
    }

    #[Route(
        path: '{_locale}/reservation/{reservation}/payment/cancelled',
        name: 'cancel_url')]
    #[Route(
        path: [
            'en' => '{_locale}/reservation/{reservation}/payment/cancelled',
            'es' => '{_locale}/reserva/{reservation}/pago/cancelado',
            'fr' => '{_locale}/reservation/{reservation}/paiement/annule'],
        priority: 10,
        name: 'cancel_url',
        requirements: ['reservation' => '\d+'])]
    public function cancelUrl(
                        Reservation $reservation,
                        string $locale = 'es',
                        string $_locale = null): Response
    {
        $locale = $_locale ? $_locale : $locale;
        $urlArray = $this->languageMenuHelper->cancelledPaymentReservationMenuLanguage($locale, $reservation);

        return $this->render('reservation/reservationPaymentCancel.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
        ]);
    }

    #[Route(
        path: '/reservation/{reservation}/options',
        options: ['expose' => true],
        name: 'get_previous_options')]
    public function getPreviousOptions(
        Request $request,
        Reservation $reservation): Response
    {
        $lang = $this->langRepository->findOneBy([
            'iso_code' => $request->getLocale(),
        ]);
        $options = $this->reservationHelper->getReservationOptions($reservation, $lang);

        return $this->json([
            'options' => $options,
        ], 200, [], ['groups' => 'main']);
    }
}