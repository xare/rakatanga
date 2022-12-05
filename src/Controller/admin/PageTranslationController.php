<?php

namespace App\Controller\admin;

use App\Entity\PageTranslation;
use App\Form\PageTranslationType;
use App\Repository\PageTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/page/translation')]
class PageTranslationController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/', name: 'page_translation_index', methods: ['GET'])]
    public function index(PageTranslationRepository $pageTranslationRepository): Response
    {
        return $this->render('page_translation/index.html.twig', [
            'page_translations' => $pageTranslationRepository->findAll(),
        ]);
    }

    #[Route(path: '/new', name: 'page_translation_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $pageTranslation = new PageTranslation();
        $form = $this->createForm(PageTranslationType::class, $pageTranslation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($pageTranslation);
            $this->entityManager->flush();

            return $this->redirectToRoute('page_translation_index');
        }

        return $this->render('page_translation/new.html.twig', [
            'page_translation' => $pageTranslation,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'page_translation_show', methods: ['GET'])]
    public function show(PageTranslation $pageTranslation): Response
    {
        return $this->render('page_translation/show.html.twig', [
            'page_translation' => $pageTranslation,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'page_translation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PageTranslation $pageTranslation): Response
    {
        $form = $this->createForm(PageTranslationType::class, $pageTranslation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('page_translation_index');
        }

        return $this->render('page_translation/edit.html.twig', [
            'page_translation' => $pageTranslation,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'page_translation_delete', methods: ['POST'])]
    public function delete(Request $request, PageTranslation $pageTranslation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pageTranslation->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($pageTranslation);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('page_translation_index');
    }
}
