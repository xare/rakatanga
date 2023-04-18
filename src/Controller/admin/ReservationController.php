<?php

namespace App\Controller\admin;

use App\Entity\Options;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\OptionsRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/reservation')]
class ReservationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ReservationRepository $reservationRepository,
        private PaginatorInterface $paginator,
        private UserRepository $userRepository)
    {

    }

    #[Route(
        path: '/',
        name: 'reservation_index',
        methods: ['GET'])]
    public function index(
        Request $request
        ): Response {
        /* if(!$pageNumber = $request->query->get('page')){
            $pageNumber = 0;
        } */
        $query = $this->reservationRepository->listAll();
        $items = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
        $latestReservation = $this->reservationRepository->getLatestReservation();
        $renderArray = [];
        $renderArray['latestReservation'] = ($latestReservation!= null)?: '';
        $renderArray['reservations'] = $items;
        return $this->render('admin/reservation/index.html.twig', $renderArray);
    }

    #[Route(
        path: '/new',
        name: 'reservation_new',
        methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();

            return $this->redirectToRoute('reservation_index');
        }
        return $this->render('admin/reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/search/{categoryName}',
        name: 'reservation_by_category',
        methods: ['GET', 'POST'])]
    public function searchByCategoryName(
        Request $request,
        string $categoryName)
    {
        $reservations = $this->paginator->paginate(
            $this->reservationRepository->listReservationsByCategory($categoryName),
            $request->query->getInt('page', 1),
            10
        );
        $latestReservation = $this->reservationRepository->getLatestReservation();
        return $this->render('admin/reservation/index.html.twig', [
            'reservations' => $reservations,
            'latestReservation' => $latestReservation,
            'count' => 10,
        ]);
    }

    #[Route(path: '/items', methods: 'GET', name: 'reservation_items')]
    public function getReservationItems(Request $request, ReservationRepository $reservationRepository, OptionsRepository $optionsRepository)
    {
        if (!$presentPage = $request->query->get('page')) {
            $presentPage = 1;
        }

        $allItems = $reservationRepository->listIndex();
        $properties = [
            'id',
            'Apellidos',
            'Nombre',
            'Email',
            'Titulo',
            'Pilotos',
            'Acompanantes',
            'FechaReserva',
            'FechaPago',
            'status',
        ];

        // GET OPTIONS

        $i = 0;
        foreach ($allItems as $item) {
            $allItems[$i]['options'] = $optionsRepository->listOptions($item['id'], 'es');
            ++$i;
        }

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

    #[Route(path: '/{id}', name: 'reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('admin/reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route(
        path: '/{id}/edit',
        name: 'reservation_edit',
        methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Reservation $reservation): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        // dd($form);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userRepository->findOneBy(
                ['email' => $request->request->get('user')]
            );
            $reservation->setUser($user);
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();

            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('admin/reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($reservation);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('reservation_index');
    }

    #[Route(path: 'ajax/reservation/send-checkin-message/{reservation}', options: ['expose' => true], name: 'ajax-reservation-send-checkin-message')]
    public function ajaxReservationSendCheckinMessage(
        Reservation $reservation,
        Mailer $mailer
    ) {
        $mailer->sendCheckinMessage($reservation);

        return new Response('send message');
    }
}
