<?php 

namespace App\Service;

use App\Entity\Options;
use App\Entity\Reservation;
use App\Entity\ReservationOptions;
use App\Repository\OptionsRepository;
use App\Repository\OptionsTranslationsRepository;
use App\Repository\PaymentsRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;

class reservationHelper
{
    private $entityManager;
    private $optionsRepository;
    private $optionsTranslationsRepository;
    private $paymentsRepository;
    private $reservationRepository;
    private $localizationHelper;
    private invoiceHelper $invoiceHelper;

    public function __construct(
                        EntityManagerInterface $entityManager,
                        OptionsRepository $optionsRepository,
                        OptionsTranslationsRepository $optionsTranslationsRepository,
                        PaymentsRepository $paymentsRepository,
                        ReservationRepository $reservationRepository,
                        localizationHelper $localizationHelper,
                        invoiceHelper $invoiceHelper)
    {
        $this->entityManager = $entityManager;
        $this->optionsRepository = $optionsRepository;
        $this->optionsTranslationsRepository = $optionsTranslationsRepository;
        $this->paymentsRepository = $paymentsRepository;
        $this->reservationRepository = $reservationRepository;
        $this->localizationHelper = $localizationHelper;
        $this->invoiceHelper = $invoiceHelper;
    }

    public function makeReservation($data,$date,$user, string $locale){
        $reservation = $this->_isReserved($user,$date);
        
        if($reservation == false) {
            $reservation = new Reservation();
            
            $reservation->setNbpilotes($data['nbPilotes']);
            $reservation->setNbAccomp($data['nbAccomp']);
            $reservation->setStatus('initialized');
            $reservation->setDate($date);
            $reservation->setUser($user);
            
            //PERSIST OPTIONS TO RESERVATION
            if($data['options'] != null && count($data['options']) > 0) {
                foreach ( $data['options'] as $option ){
                    $this->_feedReservationOptions($reservation, $option);
                }
            }
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
            $reservation->setCode(strtoupper(substr($date->getTravel()->getCategory(),0,3)) .'-'.$reservation->getId());
            $invoiceResult = $this->invoiceHelper->makeInvoice($reservation, '', [
                'name' => $user->getPrenom(). ' '.$user->getNom(),
                'address' => $user->getAddress(),
                'nif' => $user->getIdCard(),
                'postalcode' => $user->getPostcode(),
                'city' => $user->getCity(),
                'country' => $user->getCountry()
            ], $locale);
            $reservation->addInvoice($invoiceResult['invoice']);
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
        } else if ($reservation->getStatus() == 'cancelled') {
            $reservation->setStatus = 'initialized';
        }
        
        dump($reservation->getInvoices());
       return $reservation;
    }

   public function updateReservation($reservation, $data) {
        
        $reservation->setNbpilotes($data['nbpilotes']);
        $reservation->setNbAccomp($data['nbaccomp']);
        $reservation->setStatus('initialized');
        //PERSIST OPTIONS TO RESERVATION
        if( $data['finalOptions'] != null && count($data['finalOptions']) > 0 ) {
            foreach ( $data['finalOptions'] as $option ){
                $this->_feedReservationOptions($reservation, $option);
            }
        }
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();
        $this->invoiceHelper->updateInvoice($reservation);
        return $reservation;
    }

    private function _feedReservationOptions($reservation, $option){
        $optionItem = $this->optionsRepository->find($option['id']);
        dump($optionItem);
        dump($option);
        dump($reservation);
        $this->_isReservationOptionDuplicated($reservation, $option);
        $reservationOptions = new ReservationOptions();
        
        $reservationOptions->setOptions($optionItem);
        $reservationOptions->setAmmount($option['ammount']);
        $reservation->addReservationOption($reservationOptions);
    }

    private function _isReservationOptionDuplicated(Reservation $reservation, array $selectedOption) : bool
    {
        $returnValue = false;
            foreach ($reservation->getReservationOptions() as $reservedOption) {
                if(
                    $reservedOption->getOptions()->getId() === $selectedOption['id'] && 
                    $reservedOption->getReservation()->getId() === $selectedOption['id'] && 
                    $reservedOption->getAmmount() === $selectedOption['accmount']
                )
                {
                    $returnValue = true;
                }
            }
        return $returnValue;
    }

    public function getReservationOptions(
        Reservation $reservation, 
        $lang
        ){
            $optionsArray = [];
            $travelOptions = $reservation->getDate()->getTravel()->getOptions();
            $reservationOptions = $reservation->getReservationOptions();
            
            if(count($travelOptions) > 0) {
                $i=0;
                foreach ($travelOptions as $travelOption){  
                    $optionsArray[$i]['ammount'] = 0;
                    foreach( $reservationOptions as $reservationOption ){
                        foreach($travelOption->getReservationOptions() as $travelReservationOption) {
                            if ($reservationOption->getId() == $travelReservationOption->getId() ) {
                                if ($travelReservationOption->getAmmount() != 0 ) {
                                    $optionsArray[$i]['ammount'] = $travelReservationOption->getAmmount();
                                    $optionsArray[$i]['ReservationOptionId'] = $reservationOption->getId();
                                }
                            }
                        }
                    }
                    
                    $optionsArray[$i]['price'] = $travelOption->getPrice();
                    $optionsArray[$i]['id'] = $travelOption->getId();
                    
                    $optionsTranslationItem = $this->optionsTranslationsRepository->findOneBy([
                        'lang' => $lang,
                        'options' => $travelOption->getId()
                    ]);
                    if($optionsTranslationItem)
                        $optionsArray[$i]['title'] = $optionsTranslationItem->getTitle();
                    $i++; 
                }
            }
        //dd($optionsArray);
        return $optionsArray;
    }

    public function getReservedOptions(Reservation $reservation, $locale){
        $reservedOptions = [];
        $i = 0;
        foreach($reservation->getReservationOptions() as $reservationOption){
                $reservedOptions[$i]['id'] = $reservationOption->getOptions()->getId();
                $reservedOptions[$i]['ammount'] = $reservationOption->getAmmount();
                $reservedOptions[$i]['title'] = $this->localizationHelper->renderOptionString($reservationOption->getOptions()->getId(),$locale);
                $reservedOptions[$i]['price'] = $reservationOption->getOptions()->getPrice();
                $i++;
            }
        return $reservedOptions;
    }
    public function getReservationAmmount(Reservation $reservation){
        
        $total =  $this->getReservationAmmountBeforeDiscount($reservation);
        $discount = $this->getDiscount($reservation, $total);
        
        $total = $total - $discount;
        return $total;
    }

    public function getReservationAmmountBeforeDiscount(Reservation $reservation){
        $nbPilotes = $reservation->getNbpilotes();
        $prixPilote =  $reservation->getDate()->getPrixPilote();
        $nbAccomp = $reservation->getNbAccomp();
        $prixAccomp = $reservation->getDate()->getPrixaccomp();
        
        $total = ($nbPilotes * $prixPilote) + ($nbAccomp * $prixAccomp);
        
        foreach ($reservation->getReservationOptions() as $reservationOption){
            $ammount = $reservationOption->getAmmount();
            $optionPrice = $reservationOption->getOptions()->getPrice();
            $total = $total + ($ammount * $optionPrice);
        }
        return $total;
    }
    public function getDiscount($reservation, $total = 0) {
        $codepromos = $reservation->getCodespromos();
        
        $discount = 0;
        foreach ($codepromos as $codepromo) {
            if($codepromo->getMontant() > 0) {
                $discount += $codepromo->getMontant();
            } else if($codepromo->getPourcentage()){
                $discount += $total * $codepromo->getPourcentage() / 100;
            }
        }
        
        return $discount;
    }

    public function determineDuePayment (Reservation $reservation){
        $payments = $this->paymentsRepository->findBy([
            'reservation' => $reservation
        ]);

        $totalDueAmmount = 0;
        foreach($payments as $payment){
            $totalDueAmmount =+ $payment->getAmmount();
        }

        return $totalDueAmmount;
    }

    public function _isReserved($user, $date){
        $reservation = $this->reservationRepository->findOneBy([
            'user' => $user,
            'date' => $date
        ]);
        if ($reservation != null ){
            return $reservation;
        } else {
            return false;
        }
    }

}