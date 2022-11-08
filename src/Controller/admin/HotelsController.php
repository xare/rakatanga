<?php

namespace App\Controller\admin;

use App\Entity\Hotels;
use App\Form\HotelsType;
use App\Repository\HotelsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/hotels')]
class HotelsController extends AbstractController
{
    #[Route('/', name: 'hotels_index', methods: ['GET'])]
    public function index(HotelsRepository $hotelsRepository): Response
    {
        return $this->render('admin/hotels/index.html.twig', [
            'hotels' => $hotelsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'hotels_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hotel = new Hotels();
        $form = $this->createForm(HotelsType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hotel);
            $entityManager->flush();

            return $this->redirectToRoute('hotels_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/hotels/new.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'hotels_show', methods: ['GET'])]
    public function show(Hotels $hotel): Response
    {
        return $this->render('admin/hotels/show.html.twig', [
            'hotel' => $hotel,
        ]);
    }

    #[Route('/{id}/edit', name: 'hotels_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hotels $hotel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HotelsType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('hotels_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/hotels/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'hotels_delete', methods: ['POST'])]
    public function delete(Request $request, Hotels $hotel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hotel->getId(), $request->request->get('_token'))) {
            $entityManager->remove($hotel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('hotels_index', [], Response::HTTP_SEE_OTHER);
    }
}
