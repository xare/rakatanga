<?php

namespace App\Controller\admin;

use App\Entity\Payments;
use App\Form\PaymentsType;
use App\Repository\PaymentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/payments')]
class PaymentsController extends MainadminController
{

    public function __construct(
        private PaginatorInterface $paginator,
        private PaymentsRepository $paymentsRepository,
        private EntityManagerInterface $entityManager
    ){

    }

    #[Route('/', name: 'payments_index', methods: ['GET'])]
    public function index(
        Request $request): Response
    {
        $session = $request->getSession();
        $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
        $session->set('pagination_items', $pagination_items);
        $payments = $this->paginator->paginate(
            $this->paymentsRepository->listAll(),
            $request->query->getInt('page', 1),
            $pagination_items
        );

        return $this->render('admin/payments/index.html.twig', [
            'payments' => $payments,
            'count' => count($this->paymentsRepository->findAll()),
            'pagination_items' => $pagination_items
        ]);
    }

    #[Route(
        path: '/search/payments',
        name: 'search_payments',
        methods: ['GET', 'POST'])]
    public function searchByTerm(
        Request $request){
            $session = $request->getSession();
            $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
            $session->set('pagination_items', $pagination_items);

            $term = $request->request->get('term');

            $payments = $this->paginator->paginate(
                $this->paymentsRepository->listPaymentsByTerm($term),
                $request->query->getInt('page', 1),
                $pagination_items
            );
            return $this->render('admin/payments/index.html.twig', [
                'payments' => $payments,
                'count' => count($this->paymentsRepository->findAll()),
                'pagination_items' => $pagination_items
            ]);
        }


    #[Route(
        path: '/{id}/edit/',
        name: 'payments_edit',
        methods: ['GET', 'POST'],
        requirements: ['id' => '\d+'],
        priority: 10)]
    public function edit(
        Request $request,
        Payments $payment,
        EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PaymentsType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('payments_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/payments/edit.html.twig', [
            'payment' => $payment,
            'form' => $form,
        ]);
    }

    

    #[Route('/new', name: 'payments_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $payment = new Payments();
        $form = $this->createForm(PaymentsType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($payment);
            $entityManager->flush();

            return $this->redirectToRoute('payments_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/payments/new.html.twig', [
            'payment' => $payment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'payments_show', methods: ['GET'])]
    public function show(Payments $payment): Response
    {
        return $this->render('admin/payments/show.html.twig', [
            'payment' => $payment,
        ]);
    }

    #[Route('/{id}', name: 'payments_delete', methods: ['POST'])]
    public function delete(Request $request, Payments $payment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$payment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($payment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('payments_index', [], Response::HTTP_SEE_OTHER);
    }
}
