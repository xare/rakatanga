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
    public function __construct(
        private MailingsRepository $mailingsRepository,
        private PaginatorInterface $paginator,
        private EntityManagerInterface $entityManager
        ) {

        }

    #[Route(
        path: '/',
        name: 'mailings_index',
        methods: ['GET'])]
    public function index(
        Request $request ): Response
    {
        $count = count($this->mailingsRepository->findAll());
        $query = $this->mailingsRepository->listAll();
        $mailings = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/mailings/index.html.twig', [
            'mailings' => $mailings,
            'count' => $count,
        ]);
    }

    #[Route(
        path: '/search/{categoryName}',
        name: 'mailings_by_category',
        methods: ['GET', 'POST'])]
    public function searchByCategoryName(
        Request $request,
        string $categoryName
    ) {
        $mailings = $this->paginator->paginate(
            $this->mailingsRepository->listMailingsByCategory($categoryName),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/mailings/index.html.twig', [
            'mailings' => $mailings,
            'count' => 10,
        ]);
    }

    #[Route(
        path: '/new',
        name: 'mailings_new',
        methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $mailing = new Mailings();
        $form = $this->createForm(MailingsType::class, $mailing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($mailing);
            $this->entityManager->flush();

            return $this->redirectToRoute('mailings_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/mailings/new.html.twig', [
            'mailing' => $mailing,
            'form' => $form,
        ]);
    }

    #[Route(
        path:'/{id}',
        name: 'mailings_show',
        methods: ['GET'])]
    public function show(Mailings $mailing): Response
    {
        return $this->render('admin/mailings/show.html.twig', [
            'mailing' => $mailing,
        ]);
    }

    #[Route(
        path: '/{id}/edit',
        name: 'mailings_edit',
        methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Mailings $mailing): Response
    {
        $form = $this->createForm(MailingsType::class, $mailing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('admin/mailings_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/mailings/edit.html.twig', [
            'mailing' => $mailing,
            'form' => $form,
        ]);
    }

    #[Route(
        path: '/{id}',
        name: 'mailings_delete',
        methods: ['POST'])]
    public function delete(
        Request $request,
        Mailings $mailing): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mailing->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($mailing);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('mailings_index', [], Response::HTTP_SEE_OTHER);
    }
}
