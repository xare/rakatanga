<?php

namespace App\Controller\admin;

use App\Entity\Menu;
use App\Entity\Lang;
use App\Entity\Pages;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use App\Controller\admin\MainadminController;
use App\Entity\MenuTranslation;
use App\Repository\LangRepository;
use App\Repository\PagesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/menu")
 */
class MenuController extends MainadminController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/", name="menu_index", methods={"GET"})
     */
    public function index(
        MenuRepository $menuRepository, 
        Request $request,
        PaginatorInterface $paginator
        ): Response
    {
        $this->redirectToLogin($request);
        $query = $menuRepository->listAll();
        $menus = $paginator->paginate(
            $query,
            $request->query->getInt('page',1),
            10
        );
        return $this->render('admin/menu/index.html.twig', [
            'menus' => $menus
        ]);
    }

    /**
     * @Route("/new", name="menu_new", methods={"GET","POST"})
     */
    public function new(Request $request, LangRepository $langRepository, PagesRepository $pagesRepository): Response
    {
       // $this->redirectToLogin($request); 

        
        $langs = $langRepository->findAll();
        $pages = $pagesRepository->findAll();
        $menu = new Menu();

       /*  $originalMenuTranslations = new ArrayCollection();
        foreach ($menu->getMenuTranslations() as $menuTranslation) {
            $originalMenuTranslations->add($menuTranslation);
        } */
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           /*  foreach($originalMenuTranslations as $menuTranslation){
                if($menu->getMenuTranslation->contains($menuTranslation) === false){
                    $entityManager->remove($menuTranslation);
                }
            } */
            
            $this->entityManager->persist($menu);
            $this->entityManager->flush();

            return $this->redirectToRoute('menu_index');
        }

        return $this->render('admin/menu/new.html.twig', [
            'menu' => $menu,
            'langs' =>$langs,
            'pages' =>$pages,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="menu_show", methods={"GET"})
     */
    public function show(Menu $menu): Response
    {
        return $this->render('admin/menu/show.html.twig', [
            'menu' => $menu,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="menu_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Menu $menu, LangRepository $langRepository, PagesRepository $pagesRepository): Response
    {
        $langs = $langRepository->findAll();
        $pages = $pagesRepository->findAll();
        /* $originalMenuTranslations = new ArrayCollection();
        foreach ($menu->getMenuTranslations() as $menuTranslation) {
            $originalMenuTranslations->add($menuTranslation);
        } */
        $form = $this->createForm(MenuType::class, $menu);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /* foreach($originalMenuTranslations as $menuTranslation){
                if($menu->getMenuTranslation->contains($menuTranslation) === false){
                    $entityManager->remove($menuTranslation);
                }
            } */
            $this->entityManager->persist($menu);
            $this->entityManager->flush();

            return $this->redirectToRoute('menu_index');
        }

        return $this->render('admin/menu/edit.html.twig', [
            'menu' => $menu,
            'langs' => $langs,
            'pages' => $pages,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="menu_delete", methods={"POST"})
     */
    public function delete(Request $request, Menu $menu): Response
    {
        
        if ($this->isCsrfTokenValid('delete'.$menu->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($menu);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('menu_index');
    }
}