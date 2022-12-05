<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\ReservationData;
use App\Entity\ReservationOptions;
use App\Entity\Travellers;
use App\Form\ReservationDataType;
use App\Repository\DocumentRepository;
use App\Repository\LangRepository;
use App\Repository\OptionsRepository;
use App\Repository\ReservationDataRepository;
use App\Repository\ReservationOptionsRepository;
use App\Repository\TravellersRepository;
use App\Service\localizationHelper;
use App\Service\logHelper;
use App\Service\Mailer;
use App\Service\reservationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class UserFrontendReservationController extends AbstractController
{
    private $translatorInterface;
    private $breadcrumbs;

    public function __construct(TranslatorInterface $translatorInterface, Breadcrumbs $breadcrumbs){
        $this->translatorInterface = $translatorInterface;
        $this->breadcrumbs = $breadcrumbs;
    }
    #[Route(path: ['/user/reservation/{reservation}'], options: ['expose' => true], name: 'frontend_user_reservation')]
    #[Route(path: ['en' => '{_locale}/user/reservation/{reservation}', 'es' => '{_locale}/usuario/reserva/{reservation}', 'fr' => '{_locale}/utilisateur/reservation/{reservation}'], options: ['expose' => true], name: 'frontend_user_reservation')]
    public function frontend_user_reservation(
        Reservation $reservation,
        LangRepository $langRepository,
        ReservationOptionsRepository $reservationOptionsRepository,
        reservationHelper $reservationHelper,
        string $_locale = null,
        localizationHelper $localizationHelper,
        Breadcrumbs $breadcrumbs,
        RouterInterface $router,
        $locale = "es"
        
    ) {
        $user = $this->getUser();
        $locale = $_locale ? $_locale : $locale;
        $lang = $langRepository->findOneBy([
            'iso_code' => $locale
        ]);
        //Swith Locale Loader
        $otherLangsArray = $langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $urlArray[$i]['reservation'] = $reservation;
            $i++;
        }
        //End switch locale loader

        //BREADCRUMBS
       
        $breadcrumbs->addRouteItem(
            $this->translatorInterface->trans("Tus Reservas"), 
            "frontend_user_reservations",
            [
                '_locale' => $locale
            ]
        );
        $breadcrumbs->addRouteItem(
            $this->translatorInterface->trans("Tu reserva"), 
            "frontend_user_reservation",
            [
                '_locale' => $locale,
                'reservation' => $reservation
            ]
        );
        $breadcrumbs->prependRouteItem(
            $this
                ->translatorInterface
                ->trans("Inicio"),
            "index"
        );
        //END BREADCRUMBS

        $options = $reservationHelper->getReservedOptions($reservation, $lang->getIsoCode());

        $selectableOptions = $reservationHelper->getReservationOptions($reservation, $lang);
        return $this->render("reservation/index.html.twig", [
            'date' => $reservation->getDate(),
            'langs' => $urlArray,
            'locale' => $locale,
            'reservation' => $reservation,
            'selectableOptions'=> $selectableOptions,
            'options' => $options,
            'reservationOptions' => $options,
            'optionsJson' => json_encode($options),
            'isInitialized' => true,
            'userEdit' => true
        ]);
    }

     
     
    #[Route(path: '/user/reservation/{reservation}/change/option', options: ['expose' => true], name: 'user_reservation_change_option', methods: ['POST'])]
    public function userReservationChangeOption(
        Request $request,
        Reservation $reservation,
        OptionsRepository $optionsRepository,
        ReservationOptionsRepository $reservationOptionsRepository,
        EntityManagerInterface $em
    ) {
        $optionData = $request->request->get("optionData");
        $options = $optionsRepository->find($optionData['id']);
        $reservationOptions = $reservationOptionsRepository->findOneBy([
            'reservation' => $reservation,
            'options' => $optionData['id']
        ]);
        if (!$reservationOptions) {
            $reservationOptions = new ReservationOptions;
            $reservationOptions->setOptions($options);
            $reservationOptions->setReservation($reservation);
        }
        $reservationOptions->setAmmount($optionData['ammount']);
        $em->persist($reservationOptions);
        $em->flush();

        return new Response('nothing');
    }

    #[Route(path: '/user/reservation/{reservation}/change/nb', options: ['expose' => true], name: 'user_reservation_change_nb', methods: ['POST'])]
    public function userReservationChangeNb(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $em
    ) {
        $nb = $request->request->get('nb');
        $type = $request->request->get('type');
        if ($type == 'Accomp') {
            $reservation->setNbAccomp($nb);
        }
        if ($type == 'pilotes') {
            $reservation->setNbpilotes($nb);
        }
        $em->persist($reservation);
        $em->flush();
        return new Response("nb changed");
    }

    
}
