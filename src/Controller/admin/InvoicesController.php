<?php

namespace App\Controller\admin;

use App\Entity\Invoices;
use App\Form\InvoicesType;
use App\Repository\InvoicesRepository;
use App\Service\invoiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/invoices')]
class InvoicesController extends AbstractController
{

    public function __construct(private invoiceHelper $invoiceHelper) {

    }
    #[Route('/', name: 'invoices_index', methods: ['GET'])]
    public function index(InvoicesRepository $invoicesRepository): Response
    {
        return $this->render('admin/invoices/index.html.twig', [
            'invoices' => $invoicesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'invoices_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $invoice = new Invoices();
        $form = $this->createForm(InvoicesType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice = $this->invoiceHelper->createInvoiceFromNull($invoice);
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
            $entityManager->remove($invoice);
            $entityManager->flush();
        }
        $invoiceHelper->deleteInvoice($invoice);

        return $this->redirectToRoute('invoices_index');
    }
}
