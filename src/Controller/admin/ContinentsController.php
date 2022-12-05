<?php

namespace App\Controller\admin;

use App\Entity\Continents;
use App\Form\ContinentsType;
use App\Repository\ContinentsRepository;
use App\Repository\LangRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/continents')]
class ContinentsController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'continents_index', methods: ['GET'])]
    public function index(ContinentsRepository $continentsRepository): Response
    {
        return $this->render('admin/continents/index.html.twig', [
            'continents' => $continentsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'continents_new', methods: ['GET', 'POST'])]
    public function new(
                        Request $request,
                        LangRepository $langRepository
                        ): Response {
        $continent = new Continents();
        $form = $this->createForm(ContinentsType::class, $continent);
        $form->handleRequest($request);
        if (null != $request->request->get('continents')) {
            $token = $request->request->get('continents')['_token'];
            if (!$this->isCsrfTokenValid('continents__token', $token)) {
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($continent);
            $this->em->flush();

            return $this->redirectToRoute('continents_edit', [
                'id' => $continent->getId(),
            ]);
        }

        return $this->render('admin/continents/new.html.twig', [
            'continent' => $continent,
            'form' => $form->createView(),
            'langs' => $langRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'continents_show', methods: ['GET'])]
    public function show(Continents $continent): Response
    {
        return $this->render('admin/continents/show.html.twig', [
            'continent' => $continent,
        ]);
    }

    #[Route('/{id}/edit', name: 'continents_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Continents $continent,
        LangRepository $langRepository): Response
    {
        $form = $this->createForm(ContinentsType::class, $continent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form);
            $this->em->flush();

            return $this->redirectToRoute('continents_index');
        }

        return $this->render('admin/continents/edit.html.twig', [
            'continent' => $continent,
            'form' => $form->createView(),
            'langs' => $langRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'continents_delete', methods: ['POST'])]
    public function delete(Request $request, Continents $continent): Response
    {
        if ($this->isCsrfTokenValid('delete'.$continent->getCode(), $request->request->get('_token'))) {
            $this->em->remove($continent);
            $this->em->flush();
        }

        return $this->redirectToRoute('continents_index');
    }
}
