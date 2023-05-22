<?php

namespace App\Controller\admin;

use App\Entity\Document;
use App\Form\DocumentType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/document')]
class DocumentController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PaginatorInterface $paginator,
        private DocumentRepository $documentRepository)
    {
    }

    #[Route(path: '/', name: 'document_index', methods: ['GET'])]
    public function index(
        Request $request
        ): Response {
            $session = $request->getSession();
            $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
            $session->set('pagination_items', $pagination_items);

        $query = $this->documentRepository->listAll();
        $documents = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $pagination_items
        );

        return $this->render('admin/document/index.html.twig', [
            'documents' => $documents,
            'count' => count($this->documentRepository->findAll()),
            'pagination_items' => $pagination_items
        ]);
    }

    #[Route(
        path: '/search/documents',
        name: 'search_documents',
        methods: ['GET', 'POST'])]
    public function searchByTerm(
        Request $request){
            $session = $request->getSession();
            $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
            $session->set('pagination_items', $pagination_items);

            $term = $request->request->get('term');

            $documents = $this->paginator->paginate(
                $this->documentRepository->listDocumentsByTerm($term),
                $request->query->getInt('page', 1),
                $pagination_items
            );
            return $this->render('admin/document/index.html.twig', [
                'documents' => $documents,
                'count' => count($this->documentRepository->findAll()),
                'pagination_items' => $pagination_items
            ]);
        }
    #[Route(
        path: '/new',
        name: 'document_new',
        methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $document = new Document($this->getUser());
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($document);
            $this->entityManager->flush();

            return $this->redirectToRoute('document_index');
        }

        return $this->render('admin/document/new.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/{id}',
        name: 'document_show',
        methods: ['GET'])]
    public function show(Document $document): Response
    {
        return $this->render('admin/document/show.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route(
        path: '/{id}/edit',
        name: 'document_edit',
        methods: ['GET', 'POST'])]
    public function edit(Request $request, Document $document): Response
    {
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('document_index');
        }

        return $this->render('admin/document/edit.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/{id}',
        name: 'document_delete',
        methods: ['POST'])]
    public function delete(Request $request, Document $document): Response
    {
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($document);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('document_index');
    }
}
