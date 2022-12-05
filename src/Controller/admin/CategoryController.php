<?php

namespace App\Controller\admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\ContinentsRepository;
use App\Repository\ContinentTranslationRepository;
use App\Repository\LangRepository;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// Include paginator interface
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/category')]
class CategoryController extends MainadminController
{
    #[Route(path: '/', name: 'category_index', methods: ['GET'])]
    public function index(
        Request $request,
        PaginatorInterface $paginator,
        CategoryRepository $categoryRepository
        ): Response {
        $this->redirectToLogin($request);
        $query = $categoryRepository->listAll();
        $categories = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route(path: '/search/{continentCode}', name: 'category_by_continent', methods: ['GET', 'POST'])]
    public function searchByContinent(
                            Request $request,
                            string $continentCode,
                            CategoryRepository $categoryRepository,
                            PaginatorInterface $paginator,
                            ContinentsRepository $continentsRepository,
                            ContinentTranslationRepository $continentTranslationRepository)
    {
        /* $continentObject = $continentsRepository->findOneBy(['code'=>$continentCode]);
        $continentTranslationRepository->findOneBy(['continent'=>$continentObject]); */
        $categories = $paginator->paginate(
            $categoryRepository->listCategoryByContinent($continentCode),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route(path: '/new', name: 'category_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        LangRepository $langRepository,
        EntityManagerInterface $em): Response
    {
        $this->redirectToLogin($request);
        $langs = $langRepository->findAll();

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('category_edit', ['id' => $category->getId()]);
        }

        return $this->render('admin/category/new.html.twig', [
            'category' => $category,
            'langs' => $langs,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route(path: '/{id}/assign', name: 'assign_main_photo', methods: ['GET'])]
    public function assign(
                    Request $request,
                    Category $category,
                    MediaRepository $mediaRepository,
                    EntityManagerInterface $em
                    ): Response {
        $mediaId = $request->query->get('addPhoto');
        $media = $mediaRepository->find($mediaId);

        $category->setMainPhoto($media);
        $em->flush();

        return new Response($category->getId().', '.$mediaId);
    }

    #[Route(path: '/{id}/edit', name: 'category_edit', methods: ['GET', 'POST'])]
    public function edit(
                    Request $request,
                    Category $category,
                    EntityManagerInterface $em,
                    LangRepository $langRepository,
                    MediaRepository $mediaRepository
                    ): Response {
        $langs = $langRepository->findAll();
        $media = $mediaRepository->findAll();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('admin/category/edit.html.twig', [
            'langs' => $langs,
            'category' => $category,
            'media' => $media,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'category_delete', methods: ['POST'])]
    public function delete(
                        Request $request,
                        Category $category,
                        EntityManagerInterface $em
                        ): Response {
        if ($this->isCsrfTokenValid(
            'delete'.$category->getId(),
            $request->request->get('_token'))
        ) {
            $em->remove($category);
            $em->flush();
        }

        return $this->redirectToRoute('category_index');
    }
}
