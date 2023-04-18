<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Reservation;
use App\Entity\ReservationData;
use App\Entity\User;
use App\Repository\DocumentRepository;
use App\Repository\LangRepository;
use App\Repository\ReservationDataRepository;
use App\Repository\ReservationRepository;
use App\Repository\TravellersRepository;
use App\Service\languageMenuHelper;
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserFrontendReservationDocumentsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UploadHelper $uploadHelper,
        private ReservationRepository $reservationRepository,
        private ReservationDataRepository $reservationDataRepository,
        private TravellersRepository $travellersRepository,
        private DocumentRepository $documentRepository,
        private TranslatorInterface $translation,
        private languageMenuHelper $languageMenuHelper
    )
    {

    }
    #[Route(
        path: '/user/upload/document/{reservation}',
        name: 'frontend_user_upload_document')]
    #[Route(
        path: [
            'en' => '{_locale}/upload/document/{reservation}',
            'es' => '{_locale}/upload/document/{reservation}',
            'fr' => '{_locale}/upload/document/{reservation}'],
        name: 'frontend_user_upload_document')]
    public function frontend_user_upload_document(
        Request $request,
        Reservation $reservation,
        ValidatorInterface $validator,
        string $_locale = null,
        string $locale = 'es'
    ) {
        $locale = $_locale ?: $locale;
        $uploadedDocument = $request->files->get('document');
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $userId = $user->getId();
        $type = $request->request->get('type');

        $violations = $validator->validate(
            $uploadedDocument,
            [
                new NotBlank([
                    'message' => $this->translation->trans('Tu archivo no cumple las condiciones para ser subido al servidor') .'. '.$this->translation->trans('Este formulario se recargará automáticamente en 10 segundos, alternativamente, puedes dar a guardar y se salvarán los datos que has incluido en el formulario y se recargará la página.') ,
                ]),
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'image/*',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'text/plain',
                    ],
                ]),
            ]
        );
        if ($violations->count() > 0) {
            return $this->json($violations, 400);
        }
        $filename = $this->uploadHelper->uploadDocument($uploadedDocument);
        $traveller = null;
        $travellerId = null;

        if ($request->request->get('traveller') != null) {
            $travellerId = $request->request->get('traveller');

            $traveller = $this->travellersRepository->find($travellerId);
            $reservationData = $this->reservationDataRepository->findOneBy([
                'reservation' => $reservation,
                'traveller' => $traveller,
                'user' => $user,
            ]);

            if ($reservationData === null) {
                $reservationData = new ReservationData();
                $reservationData->setTraveller($traveller);
                $reservationData->setReservation($reservation);
                $reservationData->setUser($user);
            }
        } else {
            $reservationData = $this->reservationDataRepository->findOneBy([
                'reservation' => $reservation,
                'user' => $user,
            ]);
        }

        if (null === $reservationData) {
            $reservationData = new ReservationData();
            $reservationData->setReservation($reservation);
            $reservationData->setUser($user);
        }

        $document = new Document($user);
        $document->setFilename($filename);
        $document->setOriginalFilename($uploadedDocument->getClientOriginalName() ?? $filename);
        $document->setMimeType($uploadedDocument->getMimeType() ?? 'application/octet-stream');
        $document->setDoctype($type);
        $document->setReservation($reservation);
        if (null !== $traveller) {
            $document->setTraveller($traveller);
        }
        $document->setUser($user);
        $document->addReservationData($reservationData);
        $reservationData->addDocument($document);
        $this->entityManager->persist($reservationData);
        $this->entityManager->persist($document);
        $this->entityManager->flush();

        $documentsArray = [];
        $i = 0;
        foreach ($reservationData->getDocuments() as $documentItem) {
            $documentsArray[$i] = $documentItem;
            ++$i;
        }

        $renderArray = [
            'reservation' => $reservation,
            'document' => $document,
            'traveller' => $traveller,
            'user' => $user,
        ];
        $renderArray['documents'] = $documentsArray;
        $dropHtml = $this->renderView(
            'user/partials/_renderFile_in_dropzone.html.twig', $renderArray
        );
        $listHtml = $this->renderView('user/_documents_list.html.twig', $renderArray);
        if($travellerId != null) $listHtml = $this->renderView('user/_documents_list_traveller.html.twig', $renderArray);

        return $this->json(
            [
                'document' => $document,
                'dropHtml' => $dropHtml,
                'listHtml' => $listHtml,
            ],
            201,
            [],
            [
                'groups' => ['main'],
            ]
        );
    }

    #[Route(
        path: '/user/{id}/documents',
        name: 'user_add_documents')]
    #[Route(
        path: [
            'en' => '{_locale}/user/{id}/documents',
            'es' => '{_locale}/usuario/{id}/documentos',
            'fr' => '{_locale}/utilisateur/{id}/documentation'],
        name: 'user_add_documents')]
    #[IsGranted('ROLE_USER', subject: 'user')]
    public function uploadDocument(
        User $user,
        Request $request,
        ValidatorInterface $validator,
        string $locale = 'es',
        string $_locale = null
    ) {
        /** @var UploadedFile $uploadedFile */
        $uploadedDocument = $request->files->get('document');

        $violations = $validator->validate(
            $uploadedDocument,
            new File([
                'maxSize' => '5M',
                'mimeTypes' => [
                    'image/*',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'text/plain',
                ],
            ])
        );
        if ($violations->count() > 0) {
            return $this->json($violations, 400);
        }
        $filename = $this->uploadHelper->uploadDocument($uploadedDocument);

        $document = new Document($user);
        $document->setFilename($filename);
        $document->setOriginalFilename($uploadedDocument->getClientOriginalName() ?? $filename);
        $document->setMimeType($uploadedDocument->getMimeType() ?? 'application/octet-stream');

        $this->entityManager->persist($document);
        $this->entityManager->flush();

        return $this->json(
            $document,
            201,
            [],
            [
                'groups' => ['main'],
            ]
        );
    }

    #[Route(
        path: '/user/{id}/document/download',
        name: 'download_document',
        methods: ['GET'])]
    public function downloadDocument(
        Document $document,
        AuthorizationCheckerInterface $authorization
    ) {
        $user = $document->getUser();
        $currentUser = $this->getUser();
        if(
            $user->getUserIdentifier() == $currentUser->getUserIdentifier()
            || $authorization->isGranted('ROLE_ADMIN'))
        {
            //$this->denyAccessUnlessGranted('ROLE_USER', $user);
            $uploadHelper = $this->uploadHelper;
            $response = new StreamedResponse(function () use ($document, $uploadHelper) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $this->uploadHelper->readStream($document->getFilePath(), false);

            stream_copy_to_stream($fileStream, $outputStream);
        });

            $response->headers->set('Content-Type', $document->getMimeType());

            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $document->getOriginalFilename()
            );

            $response->headers->set('Content-Disposition', $disposition);
        } else {
            $response = new Response($this->translation->trans('No estás autorizado a acceder a este archivo'));
        }
        return $response;
    }

    #[Route(
        path: '/user/settings/{id}/documents',
        methods: 'GET',
        name: 'frontend_user_list_documents')]
    #[IsGranted('ROLE_USER', subject: 'user')]
    public function getDocuments(User $user)
    {
        return $this->json(
            $user->getDocuments(),
            200,
            [],
            [
                'groups' => ['main'],
            ]
        );
    }

    #[Route(
        path: '/user/reservation/{reservation}/documents',
        options: ['expose' => true],
        methods: 'GET',
        name: 'frontend_reservation_list_documents')]
    public function getReservationDocuments(
        Reservation $reservation
    ) {
        $reservationData = $this->reservationDataRepository->findOneBy(
            [
                'reservation' => $reservation,
                'User' => $this->getUser(),
            ]
        );

        $documents = [];
        if ($reservationData) {
            $documents = $reservationData->getDocuments();
        }

        return $this->json(
            $documents,
            200,
            [],
            [
                'groups' => ['main'],
            ]
        );
    }

    #[Route(
        path: '/user/documents/delete/{document}',
        name: 'frontend_user_delete_document',
        options: ['expose' => true],
        methods: ['DELETE', 'POST'])]
    public function FrontendUserDeleteDocument(
        Request $request,
        Document $document
    ) {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $type = $document->getDoctype();
        $reservationId = $request->request->get('reservationId');
        $this->entityManager->remove($document);
        $this->entityManager->flush();
        $this->uploadHelper->deleteFile($document->getFilePath(), false);
        $reservation = $this->reservationRepository->find($reservationId);

        $dropHtmlRenderArray = [
            'type' => $type,
            'reservation' => $reservation,
            'user' => $user,
        ];
        $listHtmlRenderArray = [
            'reservation' => $reservation,
            'user' => $user,
        ];
        if ($request->request->get('travellerId') !== null && $request->request->get('travellerId') != $user->getId()) {
            $travellerId = $request->request->get('travellerId');
            $dropHtmlRenderArray['traveller'] = $this->travellersRepository->find($travellerId);
            $listHtmldRenderArray['documents'] = $dropHtmlRenderArray['traveller']->getDocuments();
            $documents = $this->documentRepository->getDocumentsByReservationByTraveller($reservation, $dropHtmlRenderArray['traveller']);

        } else {
            $documents = $this->documentRepository->getDocumentsByReservationByUser($reservation);
        }

        $listHtmlRenderArray['documents'] = $documents;

        $listHtml = $this->renderView('user/_documents_list.html.twig', $listHtmlRenderArray);

        $dropHtml = $this->renderView('user/partials/_dropzone_input.html.twig', $dropHtmlRenderArray);

        return $this->json(
            [
                'listHtml' => $listHtml,
                'dropHtml' => $dropHtml,
            ],
            200,
            [],
            [
                'groups' => ['main'],
            ]
        );

        /* return new Response(null, 204); */
    }

    #[Route(
        path: [
            'en' => '{_locale}/user/documents',
            'es' => '{_locale}/usuario/documentos',
            'fr' => '{_locale}/utilisateur/documents'],
        name: 'frontend_user_documents')]
    public function userDocuments(
        string $_locale = null,
        string $locale = 'es'
    ) {
        $locale = $_locale ?: $locale;
        // Swith Locale Loader
        $urlArray = $this->languageMenuHelper->basicLanguageMenu($locale);

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $reservations = $this->reservationRepository->findBy(['user' => $user]);

        return $this->render('user/user_documents.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'user' => $user,
            'reservations' => $reservations,
            'documents' => $user->getDocuments(),
        ]);
    }
}
