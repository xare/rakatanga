<?php

namespace App\Controller\admin;

use App\Entity\Articles;
use App\Form\ArticlesType;
use App\Repository\ArticlesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/articles')]
class ArticlesController extends AbstractController
{
    public function __construct (
        private ArticlesRepository $articlesRepository,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/', name: 'articles_index', methods: ['GET'])]
    public function index(
        Request $request
        ): Response
    {
        $session = $request->getSession();
        $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
        $session->set('pagination_items', $pagination_items);

        $query = $this->articlesRepository->listAll();
        $items = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $pagination_items
        );

        return $this->render('admin/articles/index.html.twig', [
            'articles' => $items,
            'count' => count($this->articlesRepository->findAll()),
            'pagination_items' => $pagination_items
        ]);
    }
    #[Route(
        path: '/search/articles',
        name: 'search_articles',
        methods: ['GET', 'POST'])]
    public function searchByTerm(
        Request $request){
            $session = $request->getSession();
            $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
            $session->set('pagination_items', $pagination_items);

            $term = $request->request->get('term');
            $articles = $this->paginator->paginate(
                $this->articlesRepository->listArticlesByTerm($term),
                $request->query->getInt('page', 1),
                $pagination_items
            );
            return $this->render('admin/articles/index.html.twig', [
                'articles' => $articles,
                'count' => count($this->articlesRepository->findAll()),
                'pagination_items' => $pagination_items
            ]);
        }
    #[Route('/new', name: 'articles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Articles();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('articles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/articles/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'articles_show', methods: ['GET'])]
    public function show(Articles $article): Response
    {
        return $this->render('admin/articles/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'articles_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Articles $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('articles_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/articles/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'articles_delete', methods: ['POST'])]
    public function delete(Request $request, Articles $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('articles_index', [], Response::HTTP_SEE_OTHER);
    }
}
