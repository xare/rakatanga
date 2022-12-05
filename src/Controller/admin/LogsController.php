<?php

namespace App\Controller\admin;

use App\Entity\Logs;
use App\Form\LogsType;
use App\Repository\LogsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/logs')]
class LogsController extends MainadminController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'logs_index', methods: ['GET'])]
    public function index(
        Request $request,
        PaginatorInterface $paginator,
        LogsRepository $logsRepository
    ): Response {
        $count = count($logsRepository->findAll());
        $query = $logsRepository->listAll();
        $items = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/logs/index.html.twig', [
            'logs' => $items,
        ]);
    }

    #[Route('/search/{entity}', name: 'logs_by_entity')]
    public function searchByActivity(
        Request $request,
        LogsRepository $logsRepository,
        string $entity,
        PaginatorInterface $paginator
    ): Response {
        $logs = $paginator->paginate(
            $logsRepository->listLogsByEntity($entity),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/logs/index.html.twig', [
            'logs' => $logs,
            'count' => 10,
        ]);
    }

    #[Route('/new', name: 'logs_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $log = new Logs();
        $form = $this->createForm(LogsType::class, $log);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($log);
            $this->entityManager->flush();

            return $this->redirectToRoute('logs_index');
        }

        return $this->render('admin/logs/new.html.twig', [
            'log' => $log,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'logs_show', methods: ['GET'])]
    public function show(Logs $log): Response
    {
        return $this->render('admin/logs/show.html.twig', [
            'log' => $log,
        ]);
    }

    #[Route('/{id}/edit', name: 'logs_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Logs $log): Response
    {
        $form = $this->createForm(LogsType::class, $log);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('logs_index');
        }

        return $this->render('admin/logs/edit.html.twig', [
            'log' => $log,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'logs_delete', methods: ['POST'])]
    public function delete(Request $request, Logs $log): Response
    {
        if ($this->isCsrfTokenValid('delete'.$log->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($log);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('logs_index');
    }
}
