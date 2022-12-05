<?php

namespace App\Controller\admin;

use App\Entity\Texts;
use App\Form\TextsType;
use App\Repository\LangRepository;
use App\Repository\TextsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/texts')]
class TextsController extends MainadminController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'texts_index', methods: ['GET'])]
    public function index(Request $request, TextsRepository $textsRepository): Response
    {
        if (!$pageNumber = $request->query->get('page')) {
            $pageNumber = 0;
        }

        return $this->render('admin/texts/index.html.twig', [
            'texts' => $textsRepository->findAll(),
            'pageNumber' => $pageNumber,
        ]);
    }

    #[Route('/new', name: 'texts_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LangRepository $langRepository): Response
    {
        $langs = $langRepository->findAll();
        $text = new Texts();
        $form = $this->createForm(TextsType::class, $text);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTime();
            $text->setDate($now);
            $this->entityManager->persist($text);
            $this->entityManager->flush();

            return $this->redirectToRoute('texts_index');
        }

        return $this->render('admin/texts/new.html.twig', [
            'text' => $text,
            'langs' => $langs,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/items', methods: ['GET'], name: 'texts_items')]
    public function getTextsItems(Request $request, TextsRepository $textsRepository): Response
    {
        if (!$presentPage = $request->query->get('page')) {
            $presentPage = 1;
        }

        $allItems = $textsRepository->listIndex();
        $properties = [
            'id',
            'section',
            'acronym',
            'title',
            'text',
            'date',
        ];

        $defaultPage = 1;
        $itemsPerPage = 10;

        $data = [
            'items' => $allItems,
            'count' => count($allItems),
            'presentPage' => $presentPage,
            'itemsPerPage' => $itemsPerPage,
            'defaultPage' => $defaultPage,
            'properties' => $properties,
        ];

        // dd($data);
        return $this->json($data, 200, [], ['groups' => 'main']);
    }

    #[Route('/{id}', name: 'texts_show', methods: ['GET'])]
    public function show(Texts $text): Response
    {
        return $this->render('admin/texts/show.html.twig', [
            'text' => $text,
        ]);
    }

    #[Route('/{id}/edit', name: 'texts_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Texts $text): Response
    {
        $form = $this->createForm(TextsType::class, $text);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('texts_index');
        }

        return $this->render('admin/texts/edit.html.twig', [
            'text' => $text,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'texts_delete', methods: ['POST'])]
    public function delete(Request $request, Texts $text): Response
    {
        if ($this->isCsrfTokenValid('delete'.$text->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($text);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('texts_index');
    }
}
