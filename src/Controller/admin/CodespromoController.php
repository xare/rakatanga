<?php

namespace App\Controller\admin;

use App\Entity\Codespromo;
use App\Form\CodespromoType;
use App\Repository\CodespromoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/codespromo')]
class CodespromoController extends AbstractController
{
    public function __construct(
        private PaginatorInterface $paginator,
        private CodespromoRepository $codespromoRepository
    ){

    }
    #[Route('/', name: 'codespromo_index', methods: ['GET'])]
    public function index(
        Request $request
        ): Response
    {
        $session = $request->getSession();
            $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
            $session->set('pagination_items', $pagination_items);
        $query = $this->codespromoRepository->listAll();
        $codespromos = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $pagination_items
        );

        return $this->render('admin/codespromo/index.html.twig', [
            'count' => count($this->codespromoRepository->findAll()),
            'pagination_items' => $pagination_items,
            'codespromos' => $codespromos,
        ]);
    }

    #[Route(
        path: '/search/codespromo',
        name: 'search_codespromo',
        methods: ['GET', 'POST'])]
    public function searchByTerm(
        Request $request){
            $session = $request->getSession();
            $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
            $session->set('pagination_items', $pagination_items);

            $term = $request->request->get('term');
            $codespromos = $this->paginator->paginate(
                $this->codespromoRepository->listCodespromoByTerm($term),
                $request->query->getInt('page', 1),
                $pagination_items
            );
            return $this->render('admin/codespromo/index.html.twig', [
                'codespromos' => $codespromos,
                'count' => count($this->codespromoRepository->findAll()),
                'pagination_items' => $pagination_items
            ]);
        }

    #[Route('/new', name: 'codespromo_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $codespromo = new Codespromo();
        $form = $this->createForm(CodespromoType::class, $codespromo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTime();
            $codespromo->setDateAjout($now);
            $entityManager->persist($codespromo);
            $entityManager->flush();

            return $this->redirectToRoute('codespromo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/codespromo/new.html.twig', [
            'codespromo' => $codespromo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'codespromo_show', methods: ['GET'])]
    public function show(Codespromo $codespromo): Response
    {
        return $this->render('admin/codespromo/show.html.twig', [
            'codespromo' => $codespromo,
        ]);
    }

    #[Route('/{id}/edit', name: 'codespromo_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Codespromo $codespromo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CodespromoType::class, $codespromo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('codespromo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/codespromo/edit.html.twig', [
            'codespromo' => $codespromo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'codespromo_delete', methods: ['POST'])]
    public function delete(Request $request, Codespromo $codespromo, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$codespromo->getId(), $request->request->get('_token'))) {
            $entityManager->remove($codespromo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('codespromo_index', [], Response::HTTP_SEE_OTHER);
    }
}
