<?php

namespace App\Controller\admin;

use App\Entity\OptionsTranslations;
use App\Form\OptionsTranslationsType;
use App\Repository\OptionsTranslationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Lang;
use App\Controller\admin\MainadminController;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/admin/options/translations")
 */
class OptionsTranslationsController extends MainadminController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/", name="options_translations_index", methods={"GET"})
     */
    public function index(OptionsTranslationsRepository $optionsTranslationsRepository): Response
    {
        return $this->render('admin/options_translations/index.html.twig', [
            'options_translations' => $optionsTranslationsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="options_translations_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $optionsTranslation = new OptionsTranslations();
        $form = $this->createForm(OptionsTranslationsType::class, $optionsTranslation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->entityManager->persist($optionsTranslation);
            $this->entityManager->flush();

            return $this->redirectToRoute('options_translations_index');
        }

        return $this->render('admin/options_translations/new.html.twig', [
            'options_translation' => $optionsTranslation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="options_translations_show", methods={"GET"})
     */
    public function show(OptionsTranslations $optionsTranslation): Response
    {
        return $this->render('admin/options_translations/show.html.twig', [
            'options_translation' => $optionsTranslation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="options_translations_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, OptionsTranslations $optionsTranslation): Response
    {
        $form = $this->createForm(OptionsTranslationsType::class, $optionsTranslation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('options_translations_index');
        }

        return $this->render('admin/options_translations/edit.html.twig', [
            'options_translation' => $optionsTranslation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="options_translations_delete", methods={"POST"})
     */
    public function delete(Request $request, OptionsTranslations $optionsTranslation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$optionsTranslation->getId(), $request->request->get('_token'))) {
            
            $this->entityManager->remove($optionsTranslation);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('options_translations_index');
    }
}
