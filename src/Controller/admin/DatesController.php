<?php

namespace App\Controller\admin;

use App\Entity\Dates;
use App\Form\DatesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Controller\admin\MainadminController;
use App\Entity\Travellers;
use App\Repository\DatesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route(path: '/admin/dates')]
class DatesController extends MainadminController
{

    private $datesRepository;
    private $entityManager;

    public function __construct(DatesRepository $datesRepository, EntityManagerInterface $entityManager ){
        $this->datesRepository = $datesRepository;
        $this->entityManager = $entityManager;
    }
    #[Route(path: '/', name: 'dates_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        PaginatorInterface $paginator,
    ): Response
    {
        $date = new Dates();
        if(!$pageNumber = $request->query->get('page')){
            $pageNumber = 0;
        }
        $form = $this->createForm(DatesType::class, $date);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) 
        {
            $date = $form->getData();
            $this->entityManager->persist($date);
            $this->entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->render('admin/dates/_row.html.twig', [
                    'date' => $date
                ]);
            }

            $this->addFlash('notice', 'Date Added Uploaded!!');
            return $this->redirectToRoute('media_index');
        }
        
        $count = count($this->datesRepository->findAll());
        $query = $this->datesRepository->listIndex();
        $dates = $paginator->paginate(
                $query,
                $request->query->getInt('page',1),
                15
            );

        return $this->render('admin/dates/index.html.twig', [
            'dates' => $dates,
            'pageNumber' => $pageNumber,
            'count' => $count,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/search/{categoryName}', name: 'dates_by_category', methods: ['GET', 'POST'])]
    public function searchByContinent(
        Request $request,
        string $categoryName,
        DatesRepository $datesRepository,
        PaginatorInterface $paginator){
            $dates = $paginator->paginate(
                $datesRepository->listDatesByCategory($categoryName),
                $request->query->getInt('page',1),
                10
            );
    
            return $this->render('admin/dates/index.html.twig', [
                'dates' => $dates,
                'count' => 10
            ]);
        }


    #[Route(path: '/new', name: 'dates_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $date = new Dates();
        $form = $this->createForm(DatesType::class, $date);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($date);
            $this->entityManager->flush();

            return $this->redirectToRoute('dates_index');
        }

        return $this->render('admin/dates/new.html.twig', [
            'date' => $date,
            'form' => $form->createView(),
        ]);
    }
    #[Route(path: '/items', methods: 'GET', name: 'dates_items')]
    public function getItems(Request $request, PaginatorInterface $paginator)
    {
       
        if (!$presentPage = $request->query->get('page'))
        {
            $presentPage = 1;
        }
        $items = $this->datesRepository->listIndex();
        $count = count($items);
        $properties = [
            'id', 
            'type',
            'name',
            'path',
            'filename',
            'mediaPath'];
        /* $query = $mediaRepository->listAll(); */
        $defaultPage = 1;
        $itemsPerPage = 10;
        /* $media = $paginator->paginate(
            $query,
            $request->query->getInt('page',$defaultPage),
            $itemsPerPage
        ); */
        
        
        /* $items = [];
        $i = 0;
        foreach ($allMedia as $mediaItem){
            $items[$i] = $mediaItem;
            $items[$i]['totalPath'] = $this->imagineCacheManager->getBrowserPath('media/'.$mediaItem['path'],'squared_thumbnail_small');
            //$items[$i]['totalPath'] = '/uploads/media/cache/squared_thumbnail_small/media/'.$mediaItem['path'];
            
            $i++;
        } */
        
        $data = [
            'items' => $items,
            'count' => $count,
            'presentPage' => $presentPage,
            'itemsPerPage' => $itemsPerPage,
            'defaultPage' => $defaultPage,
            'properties' => $properties,
            /* 'resolvedPath' => $resolvedPath */
        ];
        return $this->json($data,200,[],['groups'=>'main']);
    }
    #[Route(path: '/{id}', name: 'dates_show', methods: ['GET'])]
    public function show(Dates $date): Response
    {
        return $this->render('admin/dates/show.html.twig', [
            'date' => $date,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'dates_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Dates $date): Response
    {
        $form = $this->createForm(DatesType::class, $date);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('dates_index');
        }

        return $this->render('admin/dates/edit.html.twig', [
            'date' => $date,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'dates_delete', methods: ['POST'])]
    public function delete(Request $request, Dates $date): Response
    {
        if ($this->isCsrfTokenValid('delete'.$date->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($date);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('dates_index');
    }

    #[Route(path: '/api/delete/{id}', name: 'api_dates_delete', methods: ['DELETE'])]
    public function apiDeleteDate(Dates $date)
    {
        $this->entityManager->remove($date);
        $this->entityManager->flush();
        return new Response(null, \Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT);
    }
    #[Route(path: '/dates/search')]
    public function search($searchTerm){
        return new Response("search");
    }

    #[Route(path: '/dates/travellerData/{traveller}', options: ['expose' => true], name: 'date_show_traveller_data', methods: ['POST'])]
     public function showTravellerData(
         Travellers $traveller
     ){
         $html = $this->renderView('admin/dates/_traveller_reservation_data_table.html.twig',[
             'traveller'=>$traveller
         ]);
         return $this->json(
             ['html'=>$html],
             200);
         
     }
}
