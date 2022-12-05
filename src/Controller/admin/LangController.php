<?php

namespace App\Controller\admin;

use App\Entity\Lang;
use App\Form\LangType;
use App\Repository\LangRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/lang')]
class LangController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/', name: 'lang_index', methods: ['GET'])]
    public function index(LangRepository $langRepository): Response
    {
        return $this->render('admin/lang/index.html.twig', [
            'langs' => $langRepository->findAll(),
        ]);
    }

    #[Route(path: '/new', name: 'lang_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $lang = new Lang();
        $form = $this->createForm(LangType::class, $lang);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($lang);
            $this->entityManager->flush();

            return $this->redirectToRoute('lang_index');
        }

        return $this->render('admin/lang/new.html.twig', [
            'lang' => $lang,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'lang_show', methods: ['GET'])]
    public function show(Lang $lang): Response
    {
        return $this->render('admin/lang/show.html.twig', [
            'lang' => $lang,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'lang_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lang $lang): Response
    {
        $form = $this->createForm(LangType::class, $lang);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('lang_index');
        }

        return $this->render('admin/lang/edit.html.twig', [
            'lang' => $lang,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'lang_delete', methods: ['POST'])]
    public function delete(Request $request, Lang $lang): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lang->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($lang);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('lang_index');
    }
}
