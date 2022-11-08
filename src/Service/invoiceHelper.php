<?php 

namespace App\Service;

use App\Entity\Invoices;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\InvoicesRepository;
use App\Service\pdfHelper;
use Doctrine\ORM\EntityManagerInterface;

class invoiceHelper
{
    private $em;
    private $invoicesRepository;
    private $pdfHelper;
    private $uploadHelper;
    public function __construct( 
        EntityManagerInterface $em,
        InvoicesRepository $invoicesRepository,
        pdfHelper $pdfHelper,
        UploadHelper $uploadHelper)
    {
        $this->em = $em;
        $this->invoicesRepository = $invoicesRepository;
        $this->pdfHelper = $pdfHelper;
        $this->uploadHelper = $uploadHelper;
    }

    public function makeInvoice( 
        Reservation $reservation,
        string $statusChange,
        array $customerData = [],
        string $locale){
        $newInvoiceNumber = $this->_getLatestInvoiceNumber();
        $invoiceStatus = [
            'new' => true,
            'invoice' => '',
            'pdf'=>'',
            'number'=>''
        ];
        //$previousInvoice = $previousInvoice[0];
        
        $invoice = new Invoices();
            
        $dateTime = new \DateTime();
        $year = $dateTime->format('Y');
        $invoiceNumber = $year.'-'.($newInvoiceNumber+1);
        
        $invoice->setInvoiceNumber($invoiceNumber);
        $invoice->setReservation($reservation);
        $this->_calculateDueAmmount($reservation);
        
        if(!empty($customerData)) {
            ($customerData['name'] != '') ? $invoice->setName($customerData['name']) : '';
            ($customerData['nif'] != '') ? $invoice->setNif($customerData['nif']) : '';
            ($customerData['address'] != '') ? $invoice->setAddress($customerData['address']) : '';
            ($customerData['city'] != '') ? $invoice->setCity($customerData['city']) : '';
            ($customerData['country'] != '') ? $invoice->setCountry($customerData['country']) : '';
            ($customerData['postalcode'] != '') ? $invoice->setPostalcode($customerData['postalcode']) : '';
        }
                
            //CREATE PRINTED INVOICE
            

            /* ( $invoice != null ) ? $this->pdfHelper->removeInvoicePdf($invoice) : ''; */
            $invoicePdf = $this->pdfHelper->createInvoicePdf($invoice, $locale, $invoiceNumber, $statusChange);
            $invoice->setOriginalFilename($invoicePdf);
            $invoice->setFilename($invoicePdf);
            $dueAmmount = $this->_calculateDueAmmount($reservation);
            if($statusChange == 'cancelled')
                $dueAmmount = - ($dueAmmount);
            $invoice->setDueAmmount($dueAmmount);
            $invoiceStatus['invoice'] = $invoice;
            $invoice->setReservation($reservation);
            $this->em->persist($invoice);
            $this->em->persist($reservation);
            $this->em->flush();
            //END SAVE Invoice

            $invoiceStatus['pdf'] = $invoicePdf;
            $invoiceStatus['number'] = $invoiceNumber;
        return $invoiceStatus;
    }

    private function _isReserved( 
        Reservation $reservation
        ){
        $invoice = $this->invoicesRepository->findBy([
                'reservation' => $reservation
            ],[
                'date_created'=>'desc'
            ]);
        if ($invoice != null ){
            return $invoice;
        } else {
            return false;
        }
    }

    public function createCancelationInvoice(Reservation $reservation ){

        
        /* if($reservation->getInvoices()->getFilename() == ''){
            dd('inside');
        } */

    }

    private function _getLatestInvoiceNumber(){
        //$latestInvoice = $this->invoicesRepository->getLatestInvoice();
        $latestInvoice = $this->invoicesRepository->findBy([],['date_created'=>'DESC'],1,0);
        if($latestInvoice == null)
            return 1;
        return $this->_getInvoiceNumber($latestInvoice[0]);
    }

    private function _getInvoiceNumber($invoice){
        $invoiceNumber = $invoice->getInvoiceNumber();
        $invoiceNumberArray = explode('-',$invoiceNumber);
        return end($invoiceNumberArray);
    }

    private function _calculateDueAmmount($reservation){
        $nbPilotes = $reservation->getNbPilotes();
        $pilotePrice = $reservation->getDate()->getPrixPilote();
        $nbAccomp = $reservation->getNbAccomp();
        $passagerPrice = $reservation->getDate()->getPrixAccomp();

        $dueAmmount = $nbPilotes * $pilotePrice + $nbAccomp * $passagerPrice;

        foreach($reservation->getReservationOptions() as $option){
            $dueAmmount += $option->getOptions()->getPrice() * $option->getAmmount();
        } 

        return $dueAmmount;
    }

    public function deleteInvoice($invoice){
        //Obtain $invoice filename

        $path = $invoice->getFilePath();
        
        //remove from filesystem
        $this->uploadHelper->deleteFile($path, false);
    }

    public function replaceInvoice($invoice, $customerData){
        $locale = "es";
            if(!empty($customerData)) {
                ($customerData['name'] != '') ? $invoice->setName($customerData['name']) : '';
                ($customerData['nif'] != '') ? $invoice->setNif($customerData['nif']) : '';
                ($customerData['address'] != '') ? $invoice->setAddress($customerData['address']) : '';
                ($customerData['city'] != '') ? $invoice->setCity($customerData['city']) : '';
                ($customerData['country'] != '') ? $invoice->setCountry($customerData['country']) : '';
                ($customerData['postalcode'] != '') ? $invoice->setPostalcode($customerData['postalcode']) : '';
            }
            $this->deleteInvoice($invoice);
            $invoicePdf = $this->pdfHelper->createInvoicePdf($invoice, $locale, $invoice->getInvoiceNumber(), true);
            $invoice->setOriginalFilename($invoicePdf);
            $invoice->setFilename($invoicePdf);
            $this->em->persist($invoice);
        $this->em->flush();
    }

    public function updateInvoice(Reservation $reservation){
        $locale = "es";
        $invoices = $reservation->getInvoices();
        $invoicesArray = [];
        $i = 0;
         foreach ($invoices as $invoice){
            $this->deleteInvoice($invoice);
            $invoicesArray[$i]['pdf'] = $this->pdfHelper->createInvoicePdf($invoice, $locale, $invoice->getInvoiceNumber(), true);
            $invoice->setOriginalFilename($invoicesArray[$i]['pdf']);
            $invoice->setFilename($invoicesArray[$i]['pdf']);
            $this->em->persist($invoice);
            $this->em->flush();
            $i++;
        }
    }


}