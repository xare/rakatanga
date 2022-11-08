<?php

namespace App\Controller;

use App\Controller\MainController;
use App\Entity\Document;
use App\Entity\Invoices;
use App\Entity\Reservation;
use App\Entity\ReservationData;
use App\Entity\ReservationOptions;
use App\Entity\Travellers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\User;
use App\Form\ReservationDataType;
use App\Form\UserType;
use App\Repository\DatesRepository;
use App\Repository\InvoicesRepository;
use App\Repository\LangRepository;
use App\Repository\OptionsRepository;
use App\Repository\ReservationDataRepository;
use App\Repository\ReservationOptionsRepository;
use App\Repository\ReservationRepository;
use App\Repository\TravellersRepository;
use App\Service\localizationHelper;
use App\Service\Mailer;
use App\Service\reservationHelper;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class UserFrontendController extends MainController
{
    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;

    public function __construct(TranslatorInterface $translatorInterface, EntityManagerInterface $entityManager){
        $this->translator = $translatorInterface;
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/user", name="frontend_user")
     * @Route({
     *      "en": "{_locale}/user",
     *      "es": "{_locale}/usuario",
     *      "fr": "{_locale}/utilisateur"
     *      }, name="frontend_user",
     *       priority=2)
     */
    public function frontend_user(
        Request $request,
        DatesRepository $datesRepository,
        LangRepository $langRepository,
        ReservationRepository $reservationRepository,
        string $_locale = null,
        $locale = 'es'
    ) {

        $locale = $_locale ? $_locale : $locale;
        $user = $this->getUser();

        //Swith Locale Loader
        $otherLangsArray = $langRepository->findOthers($locale);
        $urlArray = [];
        $i = 0;
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $i++;
        }

        $dates = $datesRepository->listNextDates();
        $reservations = $user->getReservations();
        
        return $this->render('user/index.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'dates' => $dates,
            'reservations' => $reservations,
            'user' => $this->getUser()
        ]);
    }
    /**
     * @Route("/user/reservations/", name="frontend_user_reservations")
     * @Route({
     *      "en": "{_locale}/user/reservations/",
     *      "es": "{_locale}/usuario/reservas/",
     *      "fr": "{_locale}/utilisateur/reservations/"
     *      }, name="frontend_user_reservations",
     *       priority=2)
     */
    public function frontend_user_reservations(
        Request $request,
        LangRepository $langRepository,
        $_locale = null,
        Breadcrumbs $breadcrumbs,
        ReservationRepository $reservationRepository,
        $locale = "es",
    ) {
        $locale = $_locale ?: $locale;
        $user = $this->getUser();

        //Swith Locale Loader
        $otherLangsArray = $langRepository->findOthers($locale);
        $urlArray = [];
        $i = 0;
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $i++;
        }
        //End switch Locale loader

        //BREADCRUMBS
       
         $breadcrumbs->addRouteItem(
            $this->translator->trans("Tus Reservas"), 
            "frontend_user_reservations",
            [
                '_locale' => $locale
            ]
        );
        $breadcrumbs->prependRouteItem(
            $this
                ->translator
                ->trans("Inicio"),
            "index"
        );
        //END BREADCRUMBS

        //$reservations = $user->getReservations();
        //$reservations = $userRepository->listReservations();
        $reservations = $reservationRepository->findBy(['user' => $user], ['date_ajout'=>'DESC']);
        return $this->render('/user/user_reservations.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'reservations' => $reservations
        ]);
    }
    /**
     * @Route({
     *      "en": "{_locale}/user/settings",
     *      "es": "{_locale}/usuario/datos",
     *      "fr": "{_locale}/utilisateur/donnees"
     *      }, name="frontend_user_settings")
     */
    public function frontend_user_settings(
        Request $request,
        LangRepository $langRepository,
        string $_locale = null,
        Breadcrumbs $breadcrumbs,
        $locale = 'es'
    ) {
        
        $locale = $_locale ?: $locale;

        //Swith Locale Loader
        $otherLangsArray = $langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $i++;
        }

        //End swith locale Loader

        //BREADCRUMBS
        $breadcrumbs->addRouteItem(
            $this->translator->trans("Usuario"), 
            "frontend_user",
            [
                '_locale' => $locale
            ]
        );
        $breadcrumbs->addRouteItem(
            $this->translator->trans("Datos del usuario"), 
            "frontend_user_settings",
            [
                '_locale' => $locale
            ]
        );
        $breadcrumbs->prependRouteItem(
            $this
                ->translator
                ->trans("Inicio"),
            "index"
        );
        //END BREADCRUMBS



        //Create User Form
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        //dd($form);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            //dd($user);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('Gracias, hemos guardado tus datos correctamente.'));

            //return $this->redirectToRoute('user_index');
        }
        return $this->render('user/user_settings.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'form' => $form->createView()
        ]);
    }

    

    /**
     * @Route("/user/download/document/{document}", name="user-download-document2")
     */
    public function downloadDocument2(
        Request $request,
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
            'FACTURA-' . $invoice->getInvoiceNumber() . '.pdf'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route("/user/ajax/reservation-manager/changeNb/", name="user-ajax-reservation-manager-add-pilote")
     */

    public function reservationManagerChangeNb(
        Request $request,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $em
    ) {
        $reservationId = $request->request->get('reservationId');
        $operation = $request->request->get('operation');
        $operationArray = explode('-', $operation);
        $reservation = $reservationRepository->find($reservationId);
        $nb = 0;

        if ($operationArray[1] == "pilote") {
            $nb = $reservation->getNbpilotes();
            if ($operationArray[0] == "remove") {
                $nb--;
            }
            if ($operationArray[0] == "add") {
                $nb++;
            }
            $reservation->setNbpilotes($nb);
        }
        if ($operationArray[1] == "passager") {
            $nb = $reservation->getNbAccomp();
            if ($operationArray[0] == "remove") {
                $nb--;
            }
            if ($operationArray[0] == "add") {
                $nb++;
            }
            $reservation->setNbAccomp($nb);
        }

        $em->persist($reservation);
        $em->flush();
        return $this->json([
            'nb' => $nb
        ], 200);
    }

    /**
     * @Route("/user/ajax/reservation-manager/changeOptionAmmount/", 
     * name="user-ajax-reservation-manager-change-option-ammount")
     */

    public function reservationManagerChangeOptionAmmount(
        Request $request,
        ReservationRepository $reservationRepository,
        OptionsRepository $optionsRepository,
        ReservationOptionsRepository $reservationOptionsRepository,
        EntityManagerInterface $em
    ) {
        $reservationId = $request->request->get('reservationId');
        $optionId = $request->request->get('optionId');
        $operation = $request->request->get('operation');
        $operationArray = explode('-', $operation);
        $reservation = $reservationRepository->find($reservationId);
        $option = $optionsRepository->find($optionId);
        $reservationOption = $reservationOptionsRepository->findOneBy([
            'reservation' => $reservation,
            'options' => $option
        ]);
        $ammount = $request->request->get('ammount');

        if ($operationArray[0] == "remove") {
            $ammount--;
        }
        if ($operationArray[0] == "add") {
            $ammount++;
        }
        $reservationOption->setAmmount($ammount);


        $em->persist($reservationOption);
        $em->flush();
        return $this->json([
            'ammount' => $ammount
        ], 200);
    }

    /**
     * @Route("/user/ajax/reservation-manager/updateReservation/{reservation}", name="user-ajax-reservation-manager-update-reservation")
     */

    public function reservationManagerUpdateReservation(
        Request $request,
        Reservation $reservation,
        OptionsRepository $optionsRepository,
        ReservationOptionsRepository $reservationOptionsRepository,
        EntityManagerInterface $em
    ) {
        $pilotes = $request->request->get('pilotes');
        $Accomp = $request->request->get('Accomp');
        $reservation->setNbpilotes($pilotes);
        $reservation->setNbAccomp($Accomp);

        $options = $request->request->get("options");
        if (count($options) > 0) {
            foreach ($options as $optionItem) {
                $optionId = $optionItem['option']['id'];
                $nb = $optionItem['option']['nb'];
                $option = $optionsRepository->find($optionId);
                $reservationOptions = $reservationOptionsRepository->findOneBy([
                    'reservation' => $reservation,
                    'options' => $option
                ]);
                if (null !== $reservationOptions) {
                    $reservationOptions->setAmmount($nb);
                    $em->persist($reservationOptions);
                } else {
                    $reservationOptions = new ReservationOptions();
                    $reservationOptions->setOptions($option);
                    $reservationOptions->setAmmount($nb);
                    $reservation->addReservationOption($reservationOptions);
                    $em->persist($reservation);
                }
            }
        }
        $em->flush();

        return $this->redirectToRoute(
            "frontend_user_reservation",
            [
                '_locale' => 'es',
                'reservation' => $reservation->getId()
            ]
        );
        /* return $this->json(
                    $reservation,
                    201,
                    [],
                    [
                        'groups' => ['main']
                    ]
                ); */
    }
}
