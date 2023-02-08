<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationsController extends AbstractController
{

    public function __construct( private ReservationRepository $reservationRepository){}

    #[Route('/notifications', name: 'app_notifications')]
    public function index(): Response
    {
        return $this->render('notifications/index.html.twig', [
            'controller_name' => 'NotificationsController',
        ]);
    }

    #[Route(
        path: '/notifications/latest/reservation',
        name: 'notifications-latest-reservation',
        options: ['expose' => true],
        methods: ['GET']
        )]

    function getLatestReservation() {
        $reservation = $this->reservationRepository->getLatestReservation();
        return $this->json([
                'code' => 200,
                'reservation' => $reservation
            ],
            200,
            [],
            ['groups'=>'main']);
    }
}
