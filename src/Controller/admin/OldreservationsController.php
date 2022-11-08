<?php

namespace App\Controller\admin;

use App\Entity\Oldreservations;
use App\Entity\Travel;
use App\Form\OldreservationsType;
use App\Repository\OldreservationsRepository;
use App\Repository\TravelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/oldreservations')]
class OldreservationsController extends AbstractController
{
    private $travelRepository;
    public function __construct(TravelRepository $travelRepository) {
        $this->travelRepository = $travelRepository;
    } 
    #[Route('/', name: 'oldreservations_index', methods: ['GET'])]
    public function index(
        Request $request,
        OldreservationsRepository $oldreservationsRepository,
        PaginatorInterface $paginator): Response
    {
        $count = count($oldreservationsRepository->findAll());
        $oldreservations = $paginator->paginate(
            $oldreservationsRepository->listIndex(),
            $request->query->getInt('page',1),
            15
        );
        return $this->render('admin/oldreservations/index.html.twig', [
            'count' => $count,
            'oldreservations' => $oldreservations,
        ]);
    }

    #[Route('/new', name: 'oldreservations_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $oldreservation = new Oldreservations();
        $form = $this->createForm(OldreservationsType::class, $oldreservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($oldreservation);
            $entityManager->flush();

            return $this->redirectToRoute('oldreservations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/oldreservations/new.html.twig', [
            'oldreservation' => $oldreservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'oldreservations_show', methods: ['GET'])]
    public function show(Oldreservations $oldreservation): Response
    {
        return $this->render('admin/oldreservations/show.html.twig', [
            'oldreservation' => $oldreservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'oldreservations_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Oldreservations $oldreservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OldreservationsType::class, $oldreservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            //return $this->redirectToRoute('oldreservations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/oldreservations/edit.html.twig', [
            'oldreservation' => $oldreservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'oldreservations_delete', methods: ['POST'])]
    public function delete(Request $request, Oldreservations $oldreservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$oldreservation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($oldreservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('oldreservations_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/location-select", name="ajax_oldreservation_location_select", priority="10")
     */
    public function getDatesByTravelSelect(Request $request)
    {
        $oldReservation = new Oldreservations();
        $travelId = $request->query->get('ParentValue');
        $oldReservation->setTravel($this->travelRepository->find($travelId));
        $form = $this->createForm(OldreservationsType::class, $oldReservation);

        //no field? Return an empty response

        if(!$form->has('dates')) {
            return new Response(null, 204);
        }
        return $this->render('admin/oldreservations/_form_dates_field.html.twig',[
            'oldReservationForm' => $form->createView()
        ]);
    }

}