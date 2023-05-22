<?php

namespace App\Controller\admin;

use App\Entity\Popups;
use App\Form\PopupsType;
use App\Repository\LangRepository;
use App\Repository\PopupsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/popups')]
class PopupsController extends AbstractController
{
    public function __construct(
        private PopupsRepository $popupsRepository,
        private PaginatorInterface $paginator
    ){

    }
    #[Route('/', name: 'popups_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
        $session->set('pagination_items', $pagination_items);

        $query = $this->popupsRepository->listAll();
        $popups = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $pagination_items
        );
        return $this->render('admin/popups/index.html.twig', [
            'popups' => $popups,
            'pagination_items' => $pagination_items,
            'count' => count($this->popupsRepository->findAll())
        ]);
    }
    #[Route(
        path: '/search/popups',
        name: 'search_popups',
        methods: ['GET', 'POST'])]
    public function searchByTerm(
        Request $request){
            $session = $request->getSession();
            $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
            $session->set('pagination_items', $pagination_items);

            $term = $request->request->get('term');
            $popups = $this->paginator->paginate(
                $this->popupsRepository->listPopupsByTerm($term),
                $request->query->getInt('page', 1),
                $pagination_items
            );
            return $this->render('admin/popups/index.html.twig', [
                'popups' => $popups,
                'count' => count($this->popupsRepository->findAll()),
                'pagination_items' => $pagination_items
            ]);
        }

    #[Route('/new', name: 'popups_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LangRepository $langRepository): Response
    {
        $popup = new Popups();
        $form = $this->createForm(PopupsType::class, $popup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($popup);
            $entityManager->flush();

            return $this->redirectToRoute('popups_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/popups/new.html.twig', [
            'popup' => $popup,
            'langs' => $langRepository->findAll(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'popups_show', methods: ['GET'])]
    public function show(Popups $popup): Response
    {
        return $this->render('admin/popups/show.html.twig', [
            'popup' => $popup,
        ]);
    }

    #[Route('/{id}/edit', name: 'popups_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Popups $popup,
        EntityManagerInterface $entityManager,
        LangRepository $langRepository): Response
    {
        $form = $this->createForm(PopupsType::class, $popup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($popup);
            $entityManager->flush();

            return $this->redirectToRoute('popups_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/popups/edit.html.twig', [
            'langs' => $langRepository->findAll(),
            'popup' => $popup,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'popups_delete', methods: ['POST'])]
    public function delete(Request $request, Popups $popup, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$popup->getId(), $request->request->get('_token'))) {
            $entityManager->remove($popup);
            $entityManager->flush();
        }

        return $this->redirectToRoute('popups_index', [], Response::HTTP_SEE_OTHER);
    }
}
