<?php

namespace App\Controller\admin;

use App\Entity\Pages;
use App\Form\PagesType;
use App\Repository\PagesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Lang;
use App\Controller\admin\MainadminController;
use App\Repository\LangRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route(path: '/admin/pages')]
class PagesController extends MainadminController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route(path: '/', name: 'pages_index', methods: ['GET'])]
    public function index(
        PagesRepository $pagesRepository, 
        Request $request,
        PaginatorInterface $paginator
        ): Response
    {
        $this->redirectToLogin($request);
        
        $query = $pagesRepository->listAll();
        $pages = $paginator->paginate(
            $query,
            $request->query->getInt('page',1),
            10
        );
        return $this->render('admin/pages/index.html.twig', [
            'pages' => $pages,
        ]);
    }

    #[Route(path: '/new', name: 'pages_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LangRepository $langRepository, PagesRepository $pagesRepository): Response
    {
        $this->redirectToLogin($request); 
 
        $langs = $langRepository->findAll();

        $page = new Pages();
        
        $form = $this->createForm(PagesType::class, $page);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $now = new \DateTime();
            $page->setDateModified($now);
            $this->entityManager->persist($page);
            $this->entityManager->flush();

            return $this->redirectToRoute('pages_index');
        }
        
        return $this->render('admin/pages/new.html.twig', [
            'page' => $page,
            'langs' =>$langs,
            'form' => $form->createView(),
        ]);
    }
    #[Route(path: '/items', methods: 'GET', name: 'pages_items')]
    public function getItems(Request $request, PaginatorInterface $paginator, PagesRepository $pagesRepository)
    {

        if (!$presentPage = $request->query->get('page'))
        {
            $presentPage = 1;
        }

        $items = $pagesRepository->findAll();
        $count = count($items);
        $properties = [
            'id', 
            'type',
            'name',
            'path',
            'filename',
            'mediaPath'];

        $defaultPage = 1;
        $itemsPerPage = 10;

        $data = [
            'items' => $items,
            'count' => $count,
            'presentPage' => $presentPage,
            'itemsPerPage' => $itemsPerPage,
            'defaultPage' => $defaultPage,
            'properties' => $properties,
        ];
        return $this->json($data,200,[],['groups'=>'main']);
        /* return $this->json($mediaRepository->findAll()); */
    }
    #[Route(path: '/{id}', name: 'pages_show', methods: ['GET'])]
    public function show(Pages $page): Response
    {
        return $this->render('admin/pages/show.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'pages_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pages $page, LangRepository $langRepository): Response
    {
        $langs = $langRepository->findAll();

        $form = $this->createForm(PagesType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $page->setDateModified(new \DateTime());
            $this->entityManager->persist($page);
            $this->entityManager->flush();

            return $this->redirectToRoute('pages_index');
        }

        return $this->render('admin/pages/edit.html.twig', [
            'page' => $page,
            'langs' => $langs,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'pages_delete', methods: ['POST'])]
    public function delete(Request $request, Pages $page): Response
    {
        if ($this->isCsrfTokenValid('delete'.$page->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($page);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('pages_index');
    }

    
}
