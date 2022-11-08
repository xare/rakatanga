<?php

namespace App\Controller\admin;

use App\Entity\MenuLocation;
use App\Entity\Menu;
use App\Form\MenuLocationType;
use App\Repository\MenuLocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/menulocation')]
class MenuLocationController extends AbstractController
{
    #[Route('/', name: 'menu_location_index', methods: ['GET'])]
    public function index(MenuLocationRepository $menuLocationRepository): Response
    {
        return $this->render('admin/menu_location/index.html.twig', [
            'menu_locations' => $menuLocationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'menu_location_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $menuLocation = new MenuLocation();
        $form = $this->createForm(MenuLocationType::class, $menuLocation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($menuLocation);
            $entityManager->flush();

            return $this->redirectToRoute('menu_location_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/menu_location/new.html.twig', [
            'menu_location' => $menuLocation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'menu_location_show', methods: ['GET'])]
    public function show(MenuLocation $menuLocation): Response
    {
        return $this->render('admin/menu_location/show.html.twig', [
            'menu_location' => $menuLocation,
        ]);
    }

    #[Route('/{id}/edit', name: 'menu_location_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MenuLocation $menuLocation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MenuLocationType::class, $menuLocation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('menu_location_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/menu_location/edit.html.twig', [
            'menu_location' => $menuLocation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'menu_location_delete', methods: ['POST'])]
    public function delete(Request $request, MenuLocation $menuLocation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$menuLocation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($menuLocation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('menu_location_index', [], Response::HTTP_SEE_OTHER);
    }
}
