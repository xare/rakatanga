<?php

namespace App\Controller\admin;

use App\Entity\Logs;
use App\Repository\DocumentRepository;
use App\Repository\InvoicesRepository;
use App\Repository\LogsRepository;
use App\Repository\PaymentsRepository;
use App\Repository\ReservationRepository;
use App\Repository\TravellersRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route(path: '/admin')]
class DashboardController extends AbstractController
{
    private $entityManager;
    public function __construct (EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route(path: '/', methods: 'GET', name: 'dashboard')]
    public function index(
        PaginatorInterface $paginator,
        UserRepository $userRepository,
        ReservationRepository $reservationRepository,
        InvoicesRepository $invoicesRepository,
        PaymentsRepository $paymentsRepository,
        TravellersRepository $travellersRepository,
        DocumentRepository $documentRepository
    ) {

        return $this->render(
            'admin/dashboard/index.html.twig',
            [
                'users' => $userRepository->findBy([],  ['date_ajout' => 'DESC'], 10),
                'reservations' => $reservationRepository->findBy([],  ['date_ajout' => 'DESC'], 10),
                'payments' => $paymentsRepository->findBy([],  ['date_ajout' => 'DESC'], 10),
                'invoices' => $invoicesRepository->findBy([],  ['id' => 'DESC'], 10),
                'travellers' => $travellersRepository->findBy([],  ['date_ajout' => 'DESC'], 10),
                'documents' => $documentRepository->findBy([],  ['id' => 'DESC'], 10)
                /* 'pageNumber' => $pageNumber */
            ]
        );
    }



    #[Route(path: '/dashboard-items', methods: 'GET', name: 'dashboard_items')]
    public function getLogItems(
        Request $request, 
        LogsRepository $LogsRepository,
        PaginatorInterface $paginator)
    {

        if (!$presentPage = $request->query->get('page')) {
            $presentPage = 1;
        }

        $allLogs = $LogsRepository->listIndex();
        $count = count($allLogs);
        $properties = [
            'id',
            'entity',
            'action',
            'content',
            'data'
        ];
        /* $query = $mediaRepository->listAll(); */
        $defaultPage = 1;
        $itemsPerPage = 10;
        /* $media = $paginator->paginate(
            $query,
            $request->query->getInt('page',$defaultPage),
            $itemsPerPage
        ); */

        /*  $resolvedPath = $this->imagineCacheManager->getBrowserPath('/public/uploads/media/bullet-60c6172b9e8fa.jpg','squared_thumbnail_small');
        $items = [];
        $i = 0;
        foreach ($allMedia as $mediaItem){
            $items[$i] = $mediaItem;
            $items[$i]['totalPath'] = $this->imagineCacheManager->getBrowserPath('media/'.$mediaItem['path'],'squared_thumbnail_small');
            //$items[$i]['totalPath'] = '/uploads/media/cache/squared_thumbnail_small/media/'.$mediaItem['path'];
            
            $i++;
        } */

        $data = [
            'items' => $allLogs,
            'count' => $count,
            'presentPage' => $presentPage,
            'itemsPerPage' => $itemsPerPage,
            'defaultPage' => $defaultPage,
            'properties' => $properties,
            /* 'resolvedPath' => $resolvedPath */
        ];
        return $this->json($data, 200, [], ['groups' => 'main']);
    }
}
