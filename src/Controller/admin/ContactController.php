<?php

namespace App\Controller\admin;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/contact')]
class ContactController extends MainadminController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/', name: 'contact_index', methods: ['GET'])]
    public function index(
        Request $request,
        PaginatorInterface $paginator): Response
    {
        $repository = $this->entityManager->getRepository(Contact::class);
        $count = count($repository->findAll());
        $query = $repository->listAll();
        $contacts = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/contact/index.html.twig', [
            'contacts' => $contacts,
            'count' => $count,
        ]);
    }

    #[Route(path: '/new', name: 'contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            return $this->redirectToRoute('contact_index');
        }

        return $this->render('admin/contact/new.html.twig', [
            'contact' => $contact,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'contact_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        return $this->render('admin/contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'contact_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contact $contact): Response
    {
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('contact_index');
        }

        return $this->render('admin/contact/edit.html.twig', [
            'contact' => $contact,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'contact_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($contact);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('contact_index');
    }
}
