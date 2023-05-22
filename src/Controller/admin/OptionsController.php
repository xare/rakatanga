<?php

namespace App\Controller\admin;

use App\Entity\Lang;
use App\Entity\Options;
use App\Entity\OptionsTranslations;
use App\Form\Options1Type;
use App\Repository\LangRepository;
use App\Repository\OptionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/options')]
class OptionsController extends MainadminController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private OptionsRepository $optionsRepository,
        private PaginatorInterface $paginator)
    {
    }

    #[Route(path: '/', name: 'options_index', methods: ['GET'])]
    public function index(
        Request $request): Response
    {
        $session = $request->getSession();
        $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
        $session->set('pagination_items', $pagination_items);
        $options = $this->paginator->paginate(
            $this->optionsRepository->listAll(),
            $request->query->getInt('page', 1),
            $pagination_items
        );


        return $this->render('admin/options/index.html.twig', [
            'options' => $options,
            'count' => count($this->optionsRepository->findAll()),
            'pagination_items' => $pagination_items
        ]);
    }

    #[Route(
        path: '/search/options',
        name: 'search_options',
        methods: ['GET', 'POST'])]
    public function searchByTerm(
        Request $request){
            $session = $request->getSession();
            $pagination_items = (null !== $request->query->get('pagination_items')) ?$request->query->get('pagination_items') : $session->get('pagination_items') ;
            $session->set('pagination_items', $pagination_items);

            $term = $request->request->get('term');
            $options = $this->paginator->paginate(
                $this->optionsRepository->listOptionsByTerm($term),
                $request->query->getInt('page', 1),
                $pagination_items
            );
            return $this->render('admin/options/index.html.twig', [
                'options' => $options,
                'count' => count($this->optionsRepository->findAll()),
                'pagination_items' => $pagination_items
            ]);
        }

    #[Route(path: '/filter/{categoryName}', name: 'options_by_category', methods: ['GET', 'POST'])]
    public function searchByContinent(
        Request $request,
        string $categoryName,
        OptionsRepository $optionsRepository,
        PaginatorInterface $paginator)
    {
        $options = $paginator->paginate(
            $optionsRepository->listOptionsByCategory($categoryName),
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('admin/options/index.html.twig', [
            'options' => $options,
            'count' => 10,
        ]);
    }

    #[Route(
        path: '/new',
        name: 'options_new',
        methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        LangRepository $langRepository): Response
    {
        $langs = $langRepository->findAll();
        $option = new Options();
        $form = $this->createForm(Options1Type::class, $option);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($option);
            $this->entityManager->flush();

            return $this->redirectToRoute('options_index');
        }

        return $this->render('admin/options/new.html.twig', [
            'option' => $option,
            'langs' => $langs,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'options_show', methods: ['GET'])]
    public function show(Options $option): Response
    {
        return $this->render('admin/options/show.html.twig', [
            'option' => $option,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'options_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Options $option): Response
    {
        $langs = $this->entityManager->getRepository(Lang::class)->findAll();
        $form = $this->createForm(Options1Type::class, $option);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($option);
            $this->entityManager->flush();

            return $this->redirectToRoute('options_index');
        }

        return $this->render('admin/options/edit.html.twig', [
            'option' => $option,
            'langs' => $langs,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'options_delete', methods: ['POST'])]
    public function delete(Request $request, Options $option): Response
    {
        if ($this->isCsrfTokenValid('delete'.$option->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($option);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('options_index');
    }

    #[Route(path: '/duplicate/{id}', name: 'options_duplicate', methods: ['GET', 'POST'])]
    public function duplicate(Options $option): Response
    {
        $newOption = new Options();
        $newOption->setPrice($option->getPrice());
        $newOption->setTravel($option->getTravel());
        foreach ($option->getOptionsTranslations() as $optionTranslation) {
            $newOptionTranslation = new OptionsTranslations();
            $newOptionTranslation->setLang($optionTranslation->getLang());
            $newOptionTranslation->setTitle($optionTranslation->getTitle());
            $newOptionTranslation->setIntro($optionTranslation->getIntro());

            $newOption->addOptionsTranslation($newOptionTranslation);
            $this->entityManager->persist($newOptionTranslation);
        }

        $this->entityManager->persist($newOption);
        $this->entityManager->flush();

        return $this->redirectToRoute('options_index');
    }
}
