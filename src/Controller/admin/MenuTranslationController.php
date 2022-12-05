<?php

namespace App\Controller\admin;

use App\Entity\MenuTranslation;
use App\Form\MenuTranslationType;
use App\Repository\MenuTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/menu/translation')]
class MenuTranslationController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/', name: 'menu_translation_index', methods: ['GET'])]
    public function index(MenuTranslationRepository $menuTranslationRepository): Response
    {
        return $this->render('menu_translation/index.html.twig', [
            'menu_translations' => $menuTranslationRepository->findAll(),
        ]);
    }

    #[Route(path: '/new', name: 'menu_translation_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $menuTranslation = new MenuTranslation();
        $form = $this->createForm(MenuTranslationType::class, $menuTranslation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($menuTranslation);
            $this->entityManager->flush();

            return $this->redirectToRoute('menu_translation_index');
        }

        return $this->render('menu_translation/new.html.twig', [
            'menu_translation' => $menuTranslation,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'menu_translation_show', methods: ['GET'])]
    public function show(MenuTranslation $menuTranslation): Response
    {
        return $this->render('menu_translation/show.html.twig', [
            'menu_translation' => $menuTranslation,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'menu_translation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MenuTranslation $menuTranslation): Response
    {
        $form = $this->createForm(MenuTranslationType::class, $menuTranslation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('menu_translation_index');
        }

        return $this->render('menu_translation/edit.html.twig', [
            'menu_translation' => $menuTranslation,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'menu_translation_delete', methods: ['POST'])]
    public function delete(Request $request, MenuTranslation $menuTranslation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$menuTranslation->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($menuTranslation);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('menu_translation_index');
    }
}
