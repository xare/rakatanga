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
use App\Repository\OptionsRepository;
use App\Repository\OptionsTranslationsRepository;
use App\Repository\PaymentsRepository;
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
        private CodespromoRepository $codespromoRepository,
        private localizationHelper $localizationHelper,
        private invoiceHelper $invoiceHelper)
    {
        $this->entityManager = $entityManager;
        $this->optionsRepository = $optionsRepository;
        $this->optionsTranslationsRepository = $optionsTranslationsRepository;
        $this->paymentsRepository = $paymentsRepository;
        $this->reservationRepository = $reservationRepository;
        $this->codespromoRepository = $codespromoRepository;
        $this->localizationHelper = $localizationHelper;
        $this->invoiceHelper = $invoiceHelper;
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
                        string $locale
                        ): Reservation {
        $reservation = $this->_isReserved($user, $date);

        if ($reservation == false) {
            $reservation = new Reservation();
            /**
             * @var Codespromo $codepromo
             */
            $codepromo = $this->codespromoRepository
                            ->findOneBy([
                                'email' => $user->getEmail(),
                            ]);
            if (null == $codepromo) {
                $codepromo = $this->codespromoRepository
                                ->findOneBy([
                                    'user' => $user,
                                ]);
            }
            dump($codepromo);
            $reservation->setNbpilotes($data['nbPilotes']);
            $reservation->setNbAccomp($data['nbAccomp']);
            $reservation->setStatus('initialized');
            $reservation->setDate($date);
            $reservation->setUser($user);
            $reservation->setCodespromo($codepromo);

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
            $invoice = $this->invoiceHelper->newInvoice($reservation, $locale, [
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
        } elseif ($reservation->getStatus() == 'cancelled') {
            $reservation->setStatus = 'initialized';
        }

        return $reservation;
    }

   public function updateReservation($reservation, $data, $locale)
   {
       $reservation->setNbpilotes($data['nbpilotes']);
       $reservation->setNbAccomp($data['nbaccomp']);
       $reservation->setStatus('initialized');
       // PERSIST OPTIONS TO RESERVATION
       if ($data['finalOptions'] != null && count($data['finalOptions']) > 0) {
           foreach ($data['finalOptions'] as $option) {
               $this->_feedReservationOptions($reservation, $option);
           }
       }
       $this->entityManager->persist($reservation);
       $this->entityManager->flush();
       $this->invoiceHelper->updateReservationInvoice($reservation, $data, 'updated', $locale);

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
            $this->invoiceHelper->cancelInvoice($reservation, [
                'name' => $user->getPrenom().' '.$user->getNom(),
                'address' => $user->getAddress(),
                'nif' => $user->getIdCard(),
                'postalcode' => $user->getPostcode(),
                'city' => $user->getCity(),
                'country' => $user->getCountry(),
            ], $locale);
        } catch (\Exception $exception) {
            error_log("{$exception->getFile()}: ln {$exception->getLine()} throw error message '{$exception->getMessage()}'");
            throw $exception;

            return false;
        }

        return true;
    }

    private function _feedReservationOptions($reservation, $option)
    {
        $optionItem = $this->optionsRepository->find($option['id']);

        $this->_isReservationOptionDuplicated($reservation, $option);
        $reservationOptions = new ReservationOptions();

        $reservationOptions->setOptions($optionItem);
        $reservationOptions->setAmmount($option['ammount']);
        $reservation->addReservationOption($reservationOptions);
    }

    private function _isReservationOptionDuplicated(Reservation $reservation, array $selectedOption): bool
    {
        $returnValue = false;
        foreach ($reservation->getReservationOptions() as $reservedOption) {
            if (
                $reservedOption->getOptions()->getId() === $selectedOption['id'] &&
                $reservedOption->getReservation()->getId() === $selectedOption['id'] &&
                $reservedOption->getAmmount() === $selectedOption['accmount']
            ) {
                $returnValue = true;
            }
        }

        return $returnValue;
    }

    public function getReservationOptions(
        Reservation $reservation,
        $lang
        ) {
        $optionsArray = [];
        $travelOptions = $reservation->getDate()->getTravel()->getOptions();
        $reservationOptions = $reservation->getReservationOptions();

        if (count($travelOptions) > 0) {
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
        return $optionsArray;
    }

    public function getReservedOptions(Reservation $reservation, $locale)
    {
        $reservedOptions = [];
        $i = 0;
        foreach ($reservation->getReservationOptions() as $reservationOption) {
            $reservedOptions[$i]['id'] = $reservationOption->getOptions()->getId();
            $reservedOptions[$i]['ammount'] = $reservationOption->getAmmount();
            $reservedOptions[$i]['title'] = $this->localizationHelper->renderOptionString($reservationOption->getOptions()->getId(), $locale);
            $reservedOptions[$i]['price'] = $reservationOption->getOptions()->getPrice();
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
