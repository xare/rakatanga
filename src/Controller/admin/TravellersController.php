<?php

namespace App\Controller\admin;

use App\Entity\Travellers;
use App\Form\Travellers1Type;
use App\Repository\TravellersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/travellers')]
class TravellersController extends AbstractController
{
    #[Route('/', name: 'travellers_index', methods: ['GET'])]
    public function index(TravellersRepository $travellersRepository): Response
    {
        return $this->render('admin/travellers/index.html.twig', [
            'travellers' => $travellersRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'travellers_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $traveller = new Travellers();
        $form = $this->createForm(Travellers1Type::class, $traveller);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($traveller);
            $entityManager->flush();

            return $this->redirectToRoute('travellers_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/travellers/new.html.twig', [
            'traveller' => $traveller,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'travellers_show', methods: ['GET'])]
    public function show(Travellers $traveller): Response
    {
        return $this->render('admin/travellers/show.html.twig', [
            'traveller' => $traveller,
        ]);
    }

    #[Route('/{id}/edit', name: 'travellers_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Travellers $traveller, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Travellers1Type::class, $traveller);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('travellers_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/travellers/edit.html.twig', [
            'traveller' => $traveller,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'travellers_delete', methods: ['POST'])]
    public function delete(Request $request, Travellers $traveller, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$traveller->getId(), $request->request->get('_token'))) {
            $entityManager->remove($traveller);
            $entityManager->flush();
        }

        return $this->redirectToRoute('travellers_index', [], Response::HTTP_SEE_OTHER);
    }
}
