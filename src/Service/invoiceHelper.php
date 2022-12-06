<?php

namespace App\Service;

use App\Entity\Invoices;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\InvoicesRepository;
use App\Repository\ReservationOptionsRepository;
use Doctrine\ORM\EntityManagerInterface;

class invoiceHelper
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InvoicesRepository $invoicesRepository,
        private pdfHelper $pdfHelper,
        private UploadHelper $uploadHelper,
        private reservationDataHelper $reservationDataHelper,
        private ReservationOptionsRepository $reservationOptionsRepository
    ) {

    }

    /**
     * Create an invoice while creating a reservation.
     *
     * @param string $invoiceStatus
     */
    public function newInvoice(
        Reservation $reservation,
        string $locale,
        array $customerBillingData = []
    ): Invoices {
        return $this->_createInvoice($reservation, $customerBillingData, 'new', $locale);
    }

    /**
     * cancelInvoice function
     * Cancel an invoice, we delete the invoice and with create a new one with 0,00 € value and status become cancelled.
     */
    public function cancelInvoice(
        Reservation $reservation,
        string $locale,
        array $customerBillingData,
    ): bool {
        try {
            $this->_deleteInvoice($reservation->getInvoice);
        } catch (\Exception $exception) {
            error_log("{$exception->getFile()}: ln {{$exception->getLine()}} throw error message '{$exception->getMessage()}'");
            throw $exception;
        }
        try {
            $this->_createInvoice($reservation, $locale, $customerBillingData, 'cancelled' );
        } catch (\Exception $exception) {
            error_log("{$exception->getFile()}: ln {{$exception->getLine()}} throw error message '{$exception->getMessage()}'");
            throw $exception;
        }

        return true;
    }

    /**
     * cancelInvoice function
     * UnCancel an invoice, we delete the invoice and with create a new one with the value of the reservation and the status become "uncancelled".
     */
    public function unCancelInvoice(
        Reservation $reservation,
    ): bool {
        $reservation->setStatus('uncancelled');

        return true;
    }

    /**
     * updateInvoiceBillingData function. The user is updating the billing data. Update the invoice in DB and replace file in db with.
     */
    public function updateInvoiceBillingData(
        Reservation $reservation,
        string $locale,
        array $customerBillingData = [],
        string $invoiceStatus = 'updated Billing Data'
    ): bool {
        return true;
    }

    /**
     * updateReservationInvoice function. When a reservation is updated, we replace the invoice with a new one where we reflect the changes. This function is run before updating the reservation.
     *
     * @param array  $updatedReservationData
     * @param string $invoiceStatus
     */
    public function updateReservationInvoice(
        Reservation $reservation,
        string $locale,
        array $customerData
    ): bool {
        /*
         * \Exception $exception
         */
        try {
            if ($reservation->getInvoice() != null) {
                $this->_deleteInvoice($reservation->getInvoice());
            }
        } catch (\Exception $exception) {
            error_log("{$exception->getFile()}: ln {{$exception->getLine()}} throw error message '{$exception->getMessage()}'");
        }
        try {
            $this->_createInvoice($reservation, $locale, $customerData, 'updated' );
        } catch (\Exception $exception) {
            error_log("{$exception->getFile()}: ln {{$exception->getLine()}} throw error message '{$exception->getMessage()}'");
            throw $exception;
        }

        return true;
    }

    /**
     * _createInvoice function.
     */
    private function _createInvoice(
        Reservation $reservation,
        string $locale,
        array $customerData,
        string $status
    ): Invoices {
        /**
         * @var Invoices $invoice;
         */
        $invoice = new Invoices();

        /**
         * @var \DateTime $dateTime
         */
        $dateTime = new \DateTime();
        $latestInvoiceNumber = $this->_getLatestInvoiceNumber();

        /**
         * @var int $invoiceNumber
         */
        $invoiceNumber = $latestInvoiceNumber + 1;
        $invoice->setInvoiceNumber("{$dateTime->format('Y')}-{$invoiceNumber}");
        $invoice->setReservation($reservation);

        $this->_assignCustomerDataToInvoiceObject($invoice, $locale, $customerData );

        /**
         * @var string $invoiceFileName
         */
        $invoiceFileName = $this->pdfHelper->createInvoicePdf($invoice, $locale, $status);
        $invoice->setOriginalFilename($invoiceFileName);
        $invoice->setFilename($invoiceFileName);
        $invoice->setDueAmmount($this->reservationDataHelper->getReservationAmmount($reservation));
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return $invoice;
    }

    /**
     * _assignCustomerDataToInvoice function.
     */
    private function _assignCustomerDataToInvoiceObject(
                        Invoices $invoice, 
                        string $locale, 
                        array $customerData): mixed
    {
        if (empty($customerData)) {
            return false;
        }

        $customerData['name'] != '' ? $invoice->setName($customerData['name']) : '';
        $customerData['nif'] != '' ? $invoice->setNif($customerData['nif']) : '';
        $customerData['address'] != '' ? $invoice->setAddress($customerData['address']) : '';
        $customerData['city'] != '' ? $invoice->setCity($customerData['city']) : '';
        $customerData['country'] != '' ? $invoice->setCountry($customerData['country']) : '';
        $customerData['postalcode'] != '' ? $invoice->setPostalcode($customerData['postalcode']) : '';

        return $invoice;
    }

    /**
     * _deleteInvoice function.
     */
    private function _deleteInvoice(Invoices $invoice): mixed
    {
        try {
            $this->pdfHelper->removeInvoicePdf($invoice);
            $invoice->getReservation()->setInvoice(null);
            $this->entityManager->remove($invoice);
            $this->entityManager->flush();

            return true;
        } catch (\Exception $exception) {
            error_log("{$exception->getFile()}: ln {{$exception->getLine()}} throw error message '{$exception->getMessage()}'");
            throw $exception;
        }
    }

    /**
     * _getLatestInvoiceNumber function.
     */
    private function _getLatestInvoiceNumber(): int
    {
        $latestInvoice = $this->invoicesRepository->findBy([], ['date_created' => 'DESC'], 1, 0);
        dump($latestInvoice);

        return $latestInvoice == null ? 0 : $this->_getInvoiceNumber($latestInvoice[0]);
    }

    /**
     * _getLInvoiceNumber function
     * Invoice number contains two integers linked by a dash, the first integer is the year, and the second one is the order in which it has been created.
     * i.e. 2022-24
     *  explode(string $separator, string $string, int $limit = PHP_INT_MAX): array
     *    Returns an array of strings, each of which is a substring of string formed by splitting it on boundaries formed by the string separator.
     *  end(array|object &$array): mixed
     *   advances array's internal pointer to the last element, and returns its value.
     */
    private function _getInvoiceNumber(Invoices $invoice): int
    {
        /**
         * @var array $invoiceNumberArray
         */
        $invoiceNumberArray = explode('-', $invoice->getInvoiceNumber());
        dump($invoiceNumberArray);
        dump(end($invoiceNumberArray));

        return end($invoiceNumberArray);
    }

    /**
     * _getInvoiceYearNumber.
     *
     * Invoice number contains two integers linked by a dash, the first integer is the year, and the second one is the order in which it has been created.
     * i.e. 2022-24
     *  explode(string $separator, string $string, int $limit = PHP_INT_MAX): array
     *    Returns an array of strings, each of which is a substring of string formed by splitting it on boundaries formed by the string separator.
     */
    private function _getInvoiceYearNumber(Invoices $invoice): int
    {
        return explode('-', $invoice->getInvoiceNumber())[0];
    }
}
