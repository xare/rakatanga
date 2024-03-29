<?php

namespace App\Controller;

use App\Entity\Payments;
use App\Entity\Reservation;
use App\Entity\TransferDocument;
use App\Entity\User;
use App\Form\UserType;
use App\Manager\ReservationManager;
use App\Repository\CategoryTranslationRepository;
use App\Repository\DatesRepository;
use App\Repository\LangRepository;
use App\Repository\PaymentsRepository;
use App\Repository\TravelTranslationRepository;
use App\Service\breadcrumbsHelper;
use App\Service\invoiceHelper;
use App\Service\languageMenuHelper;
use App\Service\localizationHelper;
use App\Service\logHelper;
use App\Service\Mailer;
use App\Service\paymentHelper;
use App\Service\reservationDataHelper;
use App\Service\reservationHelper;
use App\Service\slugifyHelper;
use App\Service\travellersHelper;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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
        private invoiceHelper $invoiceHelper,
        private ReservationManager $reservationManager,
        private UploadHelper $uploadHelper,
        private string $stripePublicKey,
        private string $stripeSecretKey,

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
                        string $_locale = 'es'
    ):Response {
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
            $_locale,
            $travelTranslation,
            $categoryTranslation,
            $date);

        $this->breadcrumbsHelper->reservationBreadcrumbs($_locale, $date);

        $renderArray = [
            'locale' => $_locale,
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
            $link = sprintf('<a href="%s">'.' '.$linkMessage.'</a>', $url);
            $this->addFlash(
                'error',
                $introMessage.' '.$link
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
    #[IsGranted('ROLE_USER')]
    public function reservationPayment(
        Request $request,
        Reservation $reservation,
        AuthenticationUtils $authenticationUtils,
        FormFactoryInterface $formFactory,
        string $_locale = 'es'
        ):Response
    {
        $reservationData = $request->request->all();
        /**
        * @var User $user
        */
        $user = $this->getUser();
        dump($reservationData);
        if( isset($reservationData['comment']) && $reservationData['comment'] != '' ){
            $reservation->setComment($reservationData['comment']);
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
        }
        if (
            isset($reservationData['userEdit'])
            && $reservationData['userEdit'] == true) {

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
                $_locale);
        }
        // LANG MENU
        $urlArray = $this->languageMenuHelper->reservationPaymentMenuLanguage($_locale, $reservation);
        $this->breadcrumbsHelper->reservationPaymentBreadcrumbs($_locale, $reservation);

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
                            $this->translator->trans('Transferencia Bancaria') .' 500€' => 'bankTransfer500',
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

            /**
             * @var Invoices $invoice
             */
            if($reservation->getInvoice() == null) {
                $invoice = $this->invoiceHelper->newInvoice($reservation, $_locale, [
                    'name' => $user->getPrenom().' '.$user->getNom(),
                    'address' => $user->getAddress(),
                    'nif' => $user->getIdCard(),
                    'postalcode' => $user->getPostcode(),
                    'city' => $user->getCity(),
                    'country' => $user->getCountry(),
                ]);

            $reservation->setInvoice($invoice);
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
        }
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

                    $session = $this->paymentHelper->createStripeCheckout($reservation, $_locale, $formData, $ammount);
                    return $this->redirect($session->url, \Symfony\Component\HttpFoundation\Response::HTTP_SEE_OTHER);
                } else {
                    if ($formData['paymentMethod'] == 'bankTransfer500') {
                        $ammount = 500;
                    }
                    return $this->render('reservation/reservationPaymentBank.html.twig', [
                        'locale' => $_locale,
                        'reservation' => $reservation,
                        'langs' => $urlArray,
                        'ammount' => $ammount
                    ]);
                }
            }
        }

        $optionsArray = $this->reservationHelper->getReservationOptions($reservation, $_locale);

        return $this->render('reservation/reservationPayment.html.twig', [
            'locale' => $_locale,
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
    #[IsGranted('ROLE_USER')]
    public function successUrl(
        Request $request,
        Reservation $reservation,
        Mailer $mailer,
        string $_locale = 'es'
        ):Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $urlArray = $this->languageMenuHelper->paymentSuccessReservationMenuLanguage($_locale, $reservation);

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
            $mailer->sendReservationPaymentSuccessToUs($reservation, $_locale);
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
            'locale' => $_locale,
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
    #[IsGranted('ROLE_USER')]
    public function cancelUrl(
                        Reservation $reservation,
                        string $_locale = 'es'
                        ): Response
    {
        $urlArray = $this->languageMenuHelper->cancelledPaymentReservationMenuLanguage($_locale, $reservation);

        return $this->render('reservation/reservationPaymentCancel.html.twig', [
            'locale' => $_locale,
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

    #[Route(
        path: '/transferDocument/{transferDocument}/download',
        name: 'download_transfer_document',
        methods: ['GET'])]
        public function downloadDocument(
            TransferDocument $transferDocument,
            AuthorizationCheckerInterface $authorization
        ) {
            $user = $transferDocument->getUser();
            $currentUser = $this->getUser();
            if(
                $user->getUserIdentifier() == $currentUser->getUserIdentifier()
                || $authorization->isGranted('ROLE_ADMIN'))
            {
                //$this->denyAccessUnlessGranted('ROLE_USER', $user);
                $uploadHelper = $this->uploadHelper;
                $response = new StreamedResponse(function () use ($transferDocument, $uploadHelper) {
                    $outputStream = fopen('php://output', 'wb');
                    $fileStream = $this->uploadHelper->readStream($transferDocument->getFilePath(), false);

                    stream_copy_to_stream($fileStream, $outputStream);
                });

                $response->headers->set('Content-Type', $transferDocument->getMimeType());

                $disposition = HeaderUtils::makeDisposition(
                    HeaderUtils::DISPOSITION_ATTACHMENT,
                    $transferDocument->getOriginalFilename()
                );

                $response->headers->set('Content-Disposition', $disposition);

            } else {
                $response = new Response($this->translator->trans('No estás autorizado a acceder a este archivo'));
            }
            return $response;
        }
}