<?php

namespace App\Controller\admin;

use App\Entity\Mailings;
use App\Form\MailingsType;
use App\Repository\MailingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/mailings')]
class MailingsController extends AbstractController
{
    #[Route('/', name: 'mailings_index', methods: ['GET'])]
    public function index(
        Request $request, MailingsRepository $mailingsRepository, PaginatorInterface $paginator ): Response
    {
        $count = count($mailingsRepository->findAll());
        $query = $mailingsRepository->listAll();
        $mailings = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('admin/mailings/index.html.twig', [
            'mailings' => $mailings,
            'count'=>$count
        ]);
    }
    /**
     * @Route("/search/{categoryName}", name="mailings_by_category", methods={"GET","POST"})
     */
    public function searchByCategoryName(
        Request $request,
        string $categoryName,
        MailingsRepository $mailingsRepository,
        PaginatorInterface $paginator
    ) {
        $mailings = $paginator->paginate(
            $mailingsRepository->listMailingsByCategory($categoryName),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/mailings/index.html.twig', [
            'mailings' => $mailings,
            'count' => 10
        ]);
    }
    #[Route('/new', name: 'mailings_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mailing = new Mailings();
        $form = $this->createForm(MailingsType::class, $mailing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($mailing);
            $entityManager->flush();

            return $this->redirectToRoute('mailings_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/mailings/new.html.twig', [
            'mailing' => $mailing,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'mailings_show', methods: ['GET'])]
    public function show(Mailings $mailing): Response
    {
        return $this->render('admin/mailings/show.html.twig', [
            'mailing' => $mailing,
        ]);
    }

    #[Route('/{id}/edit', name: 'mailings_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Mailings $mailing, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MailingsType::class, $mailing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin/mailings_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/mailings/edit.html.twig', [
            'mailing' => $mailing,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'mailings_delete', methods: ['POST'])]
    public function delete(Request $request, Mailings $mailing, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mailing->getId(), $request->request->get('_token'))) {
            $entityManager->remove($mailing);
            $entityManager->flush();
        }

        return $this->redirectToRoute('mailings_index', [], Response::HTTP_SEE_OTHER);
    }
}
