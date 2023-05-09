<?php

namespace App\Controller\admin;

use App\Entity\Invoices;
use App\Form\InvoicesType;
use App\Repository\InvoicesRepository;
use App\Service\invoiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/invoices')]
class InvoicesController extends AbstractController
{

    public function __construct(
        private invoiceHelper $invoiceHelper,
        private InvoicesRepository $invoicesRepository,
        private PaginatorInterface $paginator ) {

    }
    #[Route('/', name: 'invoices_index', methods: ['GET'])]
    public function index(
        Request $request): Response
    {
        $query = $this->invoicesRepository->listAll();
        $invoices = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('admin/invoices/index.html.twig', [
            'invoices' => $invoices,
        ]);
    }

    #[Route('/new', name: 'invoices_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $invoice = new Invoices();
        $form = $this->createForm(InvoicesType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $invoice = $this->invoiceHelper->makeManualInvoice(
                $request->request->get('description'),
                $formData->getDueAmmount(),
                "es",
                [
                    'name' => $formData->getName(),
                    'nif' => $formData->getNif(),
                    'postalcode' => $formData->getPostalcode(),
                    'city' => $formData->getcity(),
                    'address' => $formData->getAddress(),
                    'country' => $formData->getCountry(),
                    'dueAmmount' =>$formData->getDueAmmount()
                ],
                "open");
            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('invoices_index');
        }

        return $this->render('admin/invoices/new.html.twig', [
            'invoice' => $invoice,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'invoices_show', methods: ['GET'])]
    public function show(Invoices $invoice): Response
    {
        return $this->render('admin/invoices/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{id}/edit', name: 'invoices_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Invoices $invoice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InvoicesType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('invoices_index');
        }

        return $this->render('admin/invoices/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'invoices_delete', methods: ['POST'])]
    public function delete(
                            Request $request,
                            Invoices $invoice,
                            invoiceHelper $invoiceHelper,
                            EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->request->get('_token'))) {
            $invoiceHelper->deleteInvoice($invoice);
            /* $entityManager->remove($invoice);
            $entityManager->flush(); */
        }


        return $this->redirectToRoute('invoices_index');
    }
}
