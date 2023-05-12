<?php

namespace App\Service;

use App\Entity\Codespromo;
use App\Entity\Dates;
use App\Entity\Invoices;
use App\Entity\Options;
use App\Entity\Reservation;
use App\Entity\ReservationOptions;
use App\Entity\User;
use App\Repository\CodespromoRepository;
use App\Repository\LangRepository;
use App\Repository\OptionsRepository;
use App\Repository\OptionsTranslationsRepository;
use App\Repository\PaymentsRepository;
use App\Repository\ReservationOptionsRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;

class reservationHelper
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OptionsRepository $optionsRepository,
        private OptionsTranslationsRepository $optionsTranslationsRepository,
        private PaymentsRepository $paymentsRepository,
        private ReservationRepository $reservationRepository,
        private ReservationOptionsRepository $reservationOptionsRepository,
        private CodespromoRepository $codespromoRepository,
        private LangRepository $langRepository,
        private localizationHelper $localizationHelper,
        private invoiceHelper $invoiceHelper,
        private travellersHelper $travellersHelper )
    {
    }

    /**
     * makeReservation function.
     *
     * @param Codespromo $codepromo
     */
    public function makeReservation(
                        array $data,
                        Dates $date,
                        User $user,
                        string $locale,
                        string $codepromoString = null
                        ): Reservation {
        $reservation = $this->_isReserved($user, $date);

        if ($reservation == false) {
            $reservation = new Reservation();
            /**
             * @var Codespromo $codepromo
             */
            /* $codepromo = $this->codespromoRepository
                            ->findOneBy([
                                'email' => $user->getEmail(),
                            ]); */
            /* if (null == $codepromo) {
                $codepromo = $this->codespromoRepository
                                ->findOneBy([
                                    'user' => $user,
                                ]);
            } */
            $codepromo = $this->codespromoRepository
                                ->findOneBy([
                                    'code' => $codepromoString,
                                ]);

            $reservation->setNbpilotes($data['nbPilotes']);
            $reservation->setNbAccomp($data['nbAccomp']);
            $reservation->setStatus('initialized');
            $reservation->setDate($date);
            $reservation->setUser($user);
            $reservation->setCodespromo($codepromo);
            /* $reservation = $this->travellersHelper->addUserToReservationAsTraveller($user,$reservation); */
            // PERSIST OPTIONS TO RESERVATION
            if ($data['options'] != null && count($data['options']) > 0) {
                foreach ($data['options'] as $option) {
                    $this->_feedReservationOptions($reservation, $option);
                }
            }
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
            $reservation->setCode(strtoupper(substr($date->getTravel()->getCategory(), 0, 3)).'-'.$reservation->getId());
            /**
             * @var Invoices $invoice
             */
            /* $invoice = $this->invoiceHelper->newInvoice($reservation, $locale, [
                'name' => $user->getPrenom().' '.$user->getNom(),
                'address' => $user->getAddress(),
                'nif' => $user->getIdCard(),
                'postalcode' => $user->getPostcode(),
                'city' => $user->getCity(),
                'country' => $user->getCountry(),
            ]);
            $reservation->setInvoice($invoice); */
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
        } elseif ($reservation->getStatus() == 'cancelled') {
            $reservation->setStatus('initialized');
        }

        return $reservation;
    }

    public function updateReservation(
        Reservation $reservation,
        array $reservationData,
        array $customerData,
        string $locale):Reservation
    {

        $reservation->setNbpilotes($reservationData['nbpilotes']);
        $reservation->setNbAccomp($reservationData['nbaccomp']);
        if(isset($reservationData['comment']) && $reservationData['comment'] != '')
            $reservation->setComment($reservationData['comment']);
        $reservation->setStatus('initialized');
        $this->_resetReservationOptions($reservation);
       // PERSIST OPTIONS TO RESERVATION
        if ($reservationData['finalOptions'] != null && count($reservationData['finalOptions']) > 0) {
            foreach ($reservationData['finalOptions'] as $option) {
                $this->_feedReservationOptions($reservation, $option);
            }
        }
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();
        $this->invoiceHelper->updateReservationInvoice($reservation->getInvoice(), $locale);

        return $reservation;
    }
    private function _resetReservationOptions(Reservation $reservation) :Reservation {
        foreach($reservation->getReservationOptions() as $reservationOption) {
            $this->entityManager->remove($reservationOption);
        }
        $this->entityManager->flush();
        return $reservation;
    }
    public function cancelReservation(Reservation $reservation, $locale)
    {
        try {
            $reservation->setStatus('cancelled');
            /**
             * @var User $user
             */
            $user = $reservation->getUser();
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();

            $this->invoiceHelper->cancelInvoice($reservation, $locale, [
                'name' => $user->getPrenom().' '.$user->getNom(),
                'address' => $user->getAddress(),
                'nif' => $user->getIdCard(),
                'postalcode' => $user->getPostcode(),
                'city' => $user->getCity(),
                'country' => $user->getCountry(),
            ]);
        } catch (\Exception $exception) {
            error_log("{$exception->getFile()}: ln {$exception->getLine()} throw error message '{$exception->getMessage()}'");
            throw $exception;

            return false;
        }

        return true;
    }

    private function _feedReservationOptions(
        Reservation $reservation,
        array $option):mixed
    {
        $optionItem = $this->optionsRepository->find($option['id']);
        $isAlready = $this->_isReservationOptionAlready($reservation, $option);
        if( $isAlready == "true" ){
            return false;
        } elseif( $isAlready == "false"){
            $reservationOptions = new ReservationOptions();
            $reservationOptions->setOption($optionItem);
        } elseif ($isAlready == "newAmmount") {
            $reservationOptions = $this->reservationOptionsRepository->findOneBy(['reservation'=> $reservation]);
        }

        $reservationOptions->setAmmount($option['ammount']);
        $reservation->addReservationOption($reservationOptions);
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();
        return $reservation;
    }

    private function _isReservationOptionAlready(
        Reservation $reservation,
        array $selectedOption): string
    {

        $returnValue = "false";
        foreach ($reservation->getReservationOptions() as $reservedOption) {

            if ( $reservedOption->getOption()->getId() === $selectedOption['id'] ){
                if($reservedOption->getAmmount() === $selectedOption['ammount']){
                    $returnValue = "true";
                }
                $returnValue = "newAmmount";
            }
        }

        return $returnValue;
    }

    public function getReservationOptions(
        Reservation $reservation,
        string $locale
        ) {
        $optionsArray = [];
        $travelOptions = $reservation->getDate()->getTravel()->getOptions();
        $reservationOptions = $reservation->getReservationOptions();
        $lang = $this->langRepository->findBy(['iso_code' => $locale]);
        if (count($travelOptions) > 0) {
            $i = 0;
            foreach($travelOptions as $travelOption) {
                $optionsArray[$i]['ammount'] = 0;
                foreach($reservationOptions as $reservationOption) {
                    if($travelOption->getId() == $reservationOption->getOption()->getId()) {
                        $optionsArray[$i]['ammount'] = $reservationOption->getAmmount();
                    }
                }
                $optionsArray[$i]['price'] = $travelOption->getPrice();
                $optionsArray[$i]['id'] = $travelOption->getId();
                /* $optionTranslation = $this->optionsTranslationsRepository->findOneBy(['option'=>$travelOption->getId(), 'lang'=>$lang]);
                $optionsArray[$i]['title'] = $optionTranslation->getTitle(); */
                $optionsArray[$i]['title'] = $this->localizationHelper->renderOptionString($travelOption->getId(), $locale);
            $i++;
            }
        }
        return $optionsArray;
        /* if (count($travelOptions) > 0) {
            $i = 0;
            foreach ($travelOptions as $travelOption) {
                $optionsArray[$i]['ammount'] = 0;
                foreach ($reservationOptions as $reservationOption) {
                    foreach ($travelOption->getReservationOptions() as $travelReservationOption) {
                        if ($reservationOption->getId() == $travelReservationOption->getId()) {
                            if ($travelReservationOption->getAmmount() != 0) {
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
                    'options' => $travelOption->getId(),
                ]);
                if ($optionsTranslationItem) {
                    $optionsArray[$i]['title'] = $optionsTranslationItem->getTitle();
                }
                ++$i;
            }
        }
        // dd($optionsArray);
        return $optionsArray; */
    }

    public function getReservedOptions(Reservation $reservation, $locale)
    {
        $reservedOptions = [];
        $i = 0;
        foreach ($reservation->getReservationOptions() as $reservationOption) {
            $reservedOptions[$i]['id'] = $reservationOption->getOption()->getId();
            $reservedOptions[$i]['ammount'] = $reservationOption->getAmmount();
            $reservedOptions[$i]['title'] = $this->localizationHelper->renderOptionString($reservationOption->getOption()->getId(), $locale);
            $reservedOptions[$i]['price'] = $reservationOption->getOption()->getPrice();
            ++$i;
        }

        return $reservedOptions;
    }

    public function _isReserved($user, $date)
    {
        $reservation = $this->reservationRepository->findOneBy([
            'user' => $user,
            'date' => $date,
        ]);
        if ($reservation != null) {
            return $reservation;
        } else {
            return false;
        }
    }
}
