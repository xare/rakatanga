<?php

namespace App\Controller\admin;

use App\Entity\Travellers;
use App\Form\Travellers1Type;
use App\Form\TravellersType;
use App\Repository\TravellersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/travellers')]
class TravellersController extends AbstractController
{
    public function __construct(
        private TravellersRepository $travellersRepository,
        private PaginatorInterface $paginator,
        private EntityManagerInterface $entityManager
    ) {

    }

    #[Route('/', name: 'travellers_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
        $session->set('pagination_items', $pagination_items);

        $query = $this->travellersRepository->listAll();
        $travellers = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $pagination_items
        );
        return $this->render('admin/travellers/index.html.twig', [
            'travellers' => $travellers,
            'pagination_items' => $pagination_items,
            'count' => count($this->travellersRepository->findAll()),
        ]);
    }

    #[Route(
        path: '/search/travellers',
        name: 'search_travellers',
        methods: ['GET', 'POST'])]
    public function searchByTerm(
        Request $request){
            $session = $request->getSession();
            $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
            $session->set('pagination_items', $pagination_items);

            $term = $request->request->get('term');
           /*  dd($this->reservationRepository->listReservationsByTerm($term)); */
            $travellers = $this->paginator->paginate(
                $this->travellersRepository->listTravellersByTerm($term),
                $request->query->getInt('page', 1),
                $pagination_items
            );
            return $this->render('admin/travellers/index.html.twig', [
                'travellers' => $travellers,
                'count' => count($this->travellersRepository->findAll()),
                'pagination_items' => $pagination_items
            ]);
        }

    #[Route('/new', name: 'travellers_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $traveller = new Travellers();
        $form = $this->createForm(TravellersType::class, $traveller);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($traveller);
            $entityManager->flush();

            return $this->redirectToRoute('travellers_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/travellers/new.html.twig', [
            'traveller' => $traveller,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'travellers_show', methods: ['GET'])]
    public function show(Travellers $traveller): Response
    {
        return $this->render('admin/travellers/show.html.twig', [
            'traveller' => $traveller,
        ]);
    }

    #[Route(
        path: '/{id}/edit',
        name: 'travellers_edit',
        methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Travellers $traveller): Response
    {
        $form = $this->createForm(TravellersType::class, $traveller);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('travellers_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/travellers/edit.html.twig', [
            'traveller' => $traveller,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'travellers_delete', methods: ['POST'])]
    public function delete(Request $request, Travellers $traveller, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$traveller->getId(), $request->request->get('_token'))) {
            $entityManager->remove($traveller);
            $entityManager->flush();
        }

        return $this->redirectToRoute('travellers_index', [], Response::HTTP_SEE_OTHER);
    }
}
