<?php

namespace App\Controller\admin;

use App\Entity\Inscriptions;
use App\Entity\User;
use App\Form\InscriptionsType;
use App\Repository\InscriptionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/inscriptions')]
class InscriptionsController extends AbstractController
{
    #[Route('/', name: 'inscriptions_index', methods: ['GET'])]
    public function index(
        Request $request,
        PaginatorInterface $paginator,
        InscriptionsRepository $inscriptionsRepository): Response
    {
        $inscriptions = $paginator->paginate(
            $inscriptionsRepository->listIndex(),
            $request->query->getInt('page', 1),
            25
        );
        return $this->render('admin/inscriptions/index.html.twig', [
            'inscriptions' => $inscriptions,
        ]);
    }

    #[Route('/new', name: 'inscriptions_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $inscription = new Inscriptions();
        $form = $this->createForm(InscriptionsType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($inscription);
            $entityManager->flush();

            return $this->redirectToRoute('inscriptions_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/inscriptions/new.html.twig', [
            'inscription' => $inscription,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'inscriptions_show', methods: ['GET'])]
    public function show(Inscriptions $inscription): Response
    {
        return $this->render('admin/inscriptions/show.html.twig', [
            'inscription' => $inscription,
        ]);
    }

    #[Route('/{id}/edit', name: 'inscriptions_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Inscriptions $inscription, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InscriptionsType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('inscriptions_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/inscriptions/edit.html.twig', [
            'inscription' => $inscription,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'inscriptions_delete', methods: ['POST'])]
    public function delete(
                        Request $request,
                        Inscriptions $inscription,
                        EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$inscription->getId(), $request->request->get('_token'))) {
            $entityManager->remove($inscription);
            $entityManager->flush();
        }

        return $this->redirectToRoute('inscriptions_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/inscription/preAddUser/{inscription}', name: 'inscriptions_preadd_user', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function inscriptionsPreAddUser(
                        Inscriptions $inscription,
                        EntityManagerInterface $entityManager,
                        UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $html = $this->renderView('admin/inscriptions/_swal_password.html.twig');

        return $this->json([
            'status' => 200,
            'message' => $html,
        ], 200);
    }

    #[Route('/inscription/addUser/{inscription}', name: 'inscriptions_add_user', options: ['expose' => true], methods: ['GET', 'POST'])]
    public function inscriptionsAddUser(
                        Request $request,
                        Inscriptions $inscription,
                        EntityManagerInterface $entityManager,
                        UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $password = $request->request->get('password') ? null : 'r4k4t4ng4';

        $user = new User();
        $user->setNom($inscription->getNom());
        $user->setPrenom($inscription->getPrenom());
        $user->setEmail($inscription->getEmail());
        $telephone = new \Adamski\Symfony\PhoneNumberBundle\Model\PhoneNumber($inscription->getTelephone());
        // $telephone = new \libphonenumber\PhoneNumber($inscription->getTelephone());
        $user->setTelephone($telephone);
        $user->setLangue($inscription->getLangue());
        $user->setPosition($inscription->getPosition());
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                'r4k4t4ng4')
        );
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'status' => 200,
            'message' => 'User has been created', ], 200);
        // return $this->redirectToRoute('user_edit', ['id'=>$user->getId()], Response::HTTP_SEE_OTHER);
    }
}
