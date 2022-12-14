<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Travellers;
use App\Form\ReservationDataType;
use App\Repository\DocumentRepository;
use App\Repository\LangRepository;
use App\Repository\ReservationDataRepository;
use App\Repository\TravellersRepository;
use App\Service\breadcrumbsHelper;
use App\Service\Mailer;
use App\Service\reservationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class UserFrontendReservationTravellersController extends AbstractController
{


    public function __construct(
            private TranslatorInterface $translatorInterface,
            private Breadcrumbs $breadcrumbs,
            private breadcrumbsHelper $breadcrumbsHelper,
            private TravellersRepository $travellersRepository)
    {

    }

   #[Route(path: ['/user/reservation/data/{reservation}/{traveller}'], name: 'frontend_user_reservation_data_traveller', requirements: ['reservation' => '\d+', 'traveller' => '\d+'])]
    #[Route(path: ['en' => '{_locale}/user/reservation/data/{reservation}/{traveller}', 'es' => '{_locale}/usuario/reserva/datos/{reservation}/{traveller}', 'fr' => '{_locale}/utilisateur/reservation/donnees/{reservation}/{traveller}'], name: 'frontend_user_reservation_data_traveller', requirements: ['reservation' => '\d+', 'traveller' => '\d+'])]
    public function frontendUserReservationDataTraveller(
        Request $request,
        Reservation $reservation,
        Travellers $traveller,
        string $_locale = null,
        LangRepository $langRepository,
        ReservationDataRepository $reservationDataRepository,
        DocumentRepository $documentRepository,
        EntityManagerInterface $em,
        Mailer $mailer,
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
        // End switch locale loader

        $reservationData = $reservationDataRepository->findOneBy([
            'reservation' => $reservation,
            'travellers' => $traveller,
        ]);

        $form = $this->createForm(ReservationDataType::class, $reservationData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reservationData = $form->getData();
            $reservationData->setReservation($reservation);
            $reservationData->setTraveller($traveller);
            $em->persist($reservationData);
            $em->flush();
            $mailer->sendEmailonDataCompletionToUs($reservation);
        }

        /* $documents = $documentRepository->getDocumentsByReservationByTraveller($reservation, $traveller); */
        $documents = $documentRepository->findBy([
            'reservation' => $reservation,
            'traveller' => $traveller,
        ]);

        $array = [];
        $i = 0;
        foreach ($reservation->getReservationData() as $reservationDatum) {
            $array[$i] = $reservationDatum;
        }
        // dd($reservationData);
        return $this->render('user/user_reservation_data.html.twig', [
            'langs' => $urlArray,
            'locale' => $locale,
            'traveller' => $traveller,
            'reservation' => $reservation,
            'reservationData' => $reservationData,
            'documents' => $documents,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/user/reservation/{reservation}/travellers/save/', name: 'user_reservation_travellers_save', requirements: ['reservation' => '\d+'], methods: ['POST'])]
    #[Route(path: ['en' => '{_locale}/user/reservation/data/{reservation}/travellers/save/', 'es' => '{_locale}/usuario/reserva/datos/{reservation}/travellers/save/', 'fr' => '{_locale}/utilisateur/reservation/donnees/{reservation}/travellers/save/'], name: 'user_reservation_travellers', requirements: ['reservation' => '\d+'], methods: ['POST'])]
    public function userReservationTravellersSave(
        Request $request,
        Reservation $reservation,
        LangRepository $langRepository,
        reservationHelper $reservationHelper,
        TravellersRepository $travellersRepository,
        EntityManagerInterface $em
    ) {
        $locale = $request->request->get('locale');
        $travellersArray = $request->request->all();

        $lang = $langRepository->findOneBy([
            'iso_code' => $locale,
        ]);
        $otherLangsArray = $langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }
        // End swith locale Loader

        $options = $reservationHelper->getReservationOptions($reservation, $lang);
        $this->addFlash('success',
            "{$this->translatorInterface->trans('Gracias, hemos guardado tus datos correctamente')}  
                            {$this->translatorInterface->trans('Puedes volver a tu reserva')}".' <a href='.$this->generateUrl('frontend_user_reservations').">{$this->translatorInterface->trans('aqu??')}".'</a>');
        dump($travellersArray);
        $isTravellerInReservation = false;
        /* foreach ($travellersArray['traveller'] as $travellerArrayItem) {
            foreach ($reservation->getTravellers() as $reservationTravellerObject) {
                dump($travellerArrayItem['email']);
                dump($reservationTravellerObject->getEmail());
                if ($travellerArrayItem['email'] == $reservationTravellerObject->getEmail()) {
                    dump("is already a user");
                    $isTravellerInReservation = true;
                } else {
                    $isTravellerInReservation = false;
                    dump("is already NOT a user");
                }
            }
            dump($isTravellerInReservation);
            if ($isTravellerInReservation == false) {
                dump($travellerArrayItem['id']);
                if($travellerArrayItem['id'] == null) {   
                    $traveller = new Travellers();
                } else {
                    dump($travellerArrayItem['id']);
                    $traveller = $this->travellersRepository->find($travellerArrayItem['id']);
                }
                dump($traveller);
                $traveller->setPrenom($travellerArrayItem['prenom']);
                $traveller->setNom($travellerArrayItem['nom']);
                $traveller->setEmail($travellerArrayItem['email']);
                $traveller->setTelephone($travellerArrayItem['telephone']);
                $traveller->setPosition($travellerArrayItem['position']);
                $traveller->setUser($this->getUser());
                $now = new \DateTime();
                $traveller->setDateAjout($now);
                $traveller->addReservation($reservation);
                $em->persist($traveller);
            }
        }  
        $em->flush(); */
       
        return $this->render('user/user_reservation_travellers.html.twig', [
            'reservation' => $reservation,
            'nbpilotes' => $reservation->getNbpilotes(),
            'nbaccomp' => $reservation->getNbAccomp(),
            'optionsJson' => json_encode($options),
            'date' => $reservation->getDate(),
            'langs' => $urlArray,
        ]);
    }

    #[Route(path: '/user/reservation/{reservation}/travellers/', name: 'user_reservation_travellers', methods: ['GET'])]
    #[Route(path: ['en' => '{_locale}/user/reservation/data/{reservation}/travellers/', 'es' => '{_locale}/usuario/reserva/datos/{reservation}/travellers/', 'fr' => '{_locale}/utilisateur/reservation/donnees/{reservation}/travellers/'], name: 'user_reservation_travellers', methods: ['GET'])]
    public function userReservationTravellers(
        Reservation $reservation,
        LangRepository $langRepository,
        reservationHelper $reservationHelper,
        string $locale = 'es',
        string $_locale = null
    ) {
        // Swith Locale Loader
        $locale = $_locale ?: $locale;
        $lang = $langRepository->findOneBy([
            'iso_code' => $locale,
        ]);
        $otherLangsArray = $langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }
        // End swith locale Loader

        $this->breadcrumbsHelper->reservationTravellersBreadcrumbs($locale);

        $options = $reservationHelper->getReservationOptions($reservation, $lang);

        return $this->render('user/user_reservation_travellers.html.twig', [
            'reservation' => $reservation,
            'nbpilotes' => $reservation->getNbpilotes(),
            'nbaccomp' => $reservation->getNbAccomp(),
            'optionsJson' => json_encode($options),
            'date' => $reservation->getDate(),
            'langs' => $urlArray,
        ]);
    }
}
