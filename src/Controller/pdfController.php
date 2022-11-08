<?php 

namespace App\Controller;

use App\Entity\Invoices;
use App\Entity\Reservation;
use App\Repository\DatesRepository;
use App\Repository\InvoicesRepository;
use App\Repository\UserRepository;
use App\Service\pdfHelper;
use App\Service\UploadHelper;
use Container5ojRi9W\getMpdfService;
use Doctrine\ORM\EntityManagerInterface;
use Mpdf\Mpdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class pdfController extends AbstractController
{
    private $mpdf;
    public function __construct(Mpdf $mpdf){
        $this->mpdf = $mpdf;
    }
    /**
     * @Route("/makepdf/", name="makepdf")
     */
    function index(
        Request $request,
        DatesRepository $datesRepository,
        UserRepository $userRepository,
        InvoicesRepository $invoicesRepository,
        EntityManagerInterface $em,
        Environment $twig,
        UploadHelper $uploadHelper, 
        pdfHelper $pdfHelper, 
        $locale = "es" ):Response
    {
        $reservation = new Reservation;
        $date = $datesRepository->find(60);
        $user = $userRepository->find(1877);
        $reservation->setDate($date);
        $reservation->setUser($user);
        $reservation->setNbpilotes(2);
        $reservation->setNbAccomp(3);


        $invoice = new Invoices;
        $invoice->setReservation($reservation);
        $invoice->setName("XXXX XXX");
        $invoice->setNif("0000000X");
        $invoice->setAddress("c. xxxxx 00");
        $invoice->setPostalCode("00000");
        $invoice->setCity("XXXXXXX");
        $invoice->setCountry("XX");
        $invoice->setInvoiceNumber("00000");
        
        $file = $pdfHelper->storeInvoicePdf($invoice, $locale, count($invoicesRepository->findAll()));

        $em->persist($invoice);
        $em->flush();
         return new Response("uploaded");
    }
}

?>