<?php

namespace App\Controller\admin;

use App\Entity\User;
use App\Form\LoadUserCsvType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\Mailer;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

#[Route(path: '/admin/user')]
class UserController extends MainadminController
{

    public function __construct(
        private EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/', name: 'user_index', methods: ['GET'])]
    public function index(Request $request,
    PaginatorInterface $paginator): Response
    {
        $this->redirectToLogin($request);
        if (!$pageNumber = $request->query->get('page')) {
            $pageNumber = 0;
        }
        $userRepository = $this->entityManager->getRepository(User::class);
        $query = $userRepository->listAll();
        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
            'pageNumber' => $pageNumber,
        ]);
    }

#[Route(path: '/user-items', methods: 'GET', name: 'user_items')]
    public function getUserItems(Request $request, PaginatorInterface $paginator, UserRepository $userRepository)
    {
        if (!$presentPage = $request->query->get('page')) {
            $presentPage = 1;
        }

        $allItems = $userRepository->listIndex();
        $properties = [
            'id',
            'email',
            'prenom',
            'nom',
            'telephone',
        ];

        $defaultPage = 1;
        $itemsPerPage = 10;

        $data = [
            'items' => $allItems,
            'count' => count($allItems),
            'presentPage' => $presentPage,
            'itemsPerPage' => $itemsPerPage,
            'defaultPage' => $defaultPage,
            'properties' => $properties,
            /* 'resolvedPath' => $resolvedPath */
        ];

        return $this->json($data, 200, [], ['groups' => 'main']);
    }

    #[Route(path: '/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(
                    Request $request,
                    UserPasswordHasherInterface $userPasswordHasher,
                    Mailer $mailer,
                    VerifyEmailHelperInterface $verifyEmailHelper): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'user_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );
            $verificationUrl = $signatureComponents->getSignedUrl();
            // WE SEND EMAILS
            $mailer->sendRegistrationVerificationToUser($user, $verificationUrl);
            $mailer->sendRegistrationToUs($user);

            return $this->redirectToRoute('user_edit', [
                'id' => $user->getId(),
            ]);
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/loadFromCsv', name: 'load_users', methods: ['GET', 'POST'])]
    public function LoadFromCsv(
        Request $request,
        UploadHelper $uploadHelper,
        UserPasswordHasherInterface $userPasswordHasher
    ) {
        $form = $this->createForm(LoadUserCsvType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $csvFile */
            $csvFile = $form->get('csvFile')->getData();
            if ($csvFile) {
                $csvUploadedFile = $uploadHelper->upload($csvFile);
                $reader = Reader::createFromPath($this->csvDirectory.'/'.$csvUploadedFile, 'r');
                $reader->setHeaderOffset(0);
                $records = $reader->getRecords();

                foreach ($records as $record) {
                    $user = new User();

                    $user->setEmail($record['email']);
                    $user->setRoles(['ROLE_USER']);
                    $user->setNom($record['nom']);
                    $user->setPrenom($record['prenom']);
                    $user->setTelephone($record['telephone']);
                    $user->setPosition($record['position']);
                    $user->setArrhes($record['arrhes']);
                    $user->setSolde($record['solde']);
                    $user->setAssurance($record['assurance']);
                    $user->setVols($record['vols']);
                    $user->setPassword($userPasswordHasher->hashPassword(
                        $user,
                        'r4k4t4ng4'
                    ));
                    $user->setLangue($record['langue']);
                    $user->setRemarque($record['remarque']);
                    $this->entityManager->persist($user);
                }
                $this->entityManager->flush();
            }
            // return $this->redirectToRoute('travel_index');
        }

        return $this->render('admin/user/_form_add_from_csv.html.twig', [
                'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        UserPasswordHasherInterface $passwordEncoder): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordEncoder->hashPassword($user, $form['password']->getData()));
            $this->entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    #[Route(path: '/search/{term}', name: 'search_user')]
    public function search(Request $request, string $term, UserRepository $userRepository): Response
    {
        if (!$presentPage = $request->query->get('page')) {
            $presentPage = 1;
        }

        $items = $userRepository->searchUser($term, 'email');
        $defaultPage = 1;
        $itemsPerPage = 10;
        $properties = [
            'id',
            'email',
            'prenom',
            'nom',
            '',
        ];
        $data = [
            'items' => $items,
            'count' => count($items),
            'presentPage' => $presentPage,
            'itemsPerPage' => $itemsPerPage,
            'defaultPage' => $defaultPage,
            'properties' => $properties,
        ];

        return $this->json($data, 200, [], ['groups' => 'main']);
    }
}
