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
use App\Service\UploadHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserFrontendReservationDocumentsController extends AbstractController
{
    #[Route(path: '/user/upload/document/{reservation}', name: 'frontend_user_upload_document')]
    #[Route(path: ['en' => '{_locale}/upload/document/{reservation}', 'es' => '{_locale}/upload/document/{reservation}', 'fr' => '{_locale}/upload/document/{reservation}'], name: 'frontend_user_upload_document')]
    public function frontend_user_upload_document(
        Request $request,
        string $_locale = null,
        Reservation $reservation,
        ReservationDataRepository $reservationDataRepository,
        UploadHelper $uploadHelper,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        TravellersRepository $travellersRepository,
        DocumentRepository $documentRepository,
        $locale = 'es'
    ) {
        $uploadedDocument = $request->files->get('document');

        $user = $this->getUser();
        /**
         * @var User $user
         */
        $userId = $user->getId();
        $type = $request->request->get('type');

        $violations = $validator->validate(
            $uploadedDocument,
            [
                new NotBlank([
                    'message' => 'Please select a file to upload',
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
        $filename = $uploadHelper->uploadDocument($uploadedDocument);
        $traveller = null;
        $travellerId = null;
        if ($request->request->get('traveller') != null) {
            $travellerId = $request->request->get('traveller');
        }

        if ($travellerId != null) {
            $traveller = $travellersRepository->find($travellerId);
            $reservationData = $reservationDataRepository->findOneBy([
                'reservation' => $reservation,
                'travellers' => $traveller,
                'User' => $user,
            ]);
            if ($reservationData === null) {
                $reservationData = new ReservationData();
                $reservationData->setTraveller($traveller);
                $reservationData->setReservation($reservation);
                $reservationData->setUser($user);
            }
        } else {
            $reservationData = $reservationDataRepository->findOneBy([
                'reservation' => $reservation,
                'User' => $user,
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
        $document->addReservationData($reservationData);
        $reservationData->addDocument($document);
        $em->persist($reservationData);
        $em->persist($document);
        $em->flush();

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
            'user/_renderFile_in_dropzone.html.twig', $renderArray
        );

        $listHtml = $this->renderView('user/_documents_list.html.twig', $renderArray);

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

    /**
     * @IsGranted("ROLE_USER", subject="user")
     */
    #[Route(path: '/user/{id}/documents', name: 'user_add_documents')]
    #[Route(path: ['en' => '{_locale}/user/{id}/documents', 'es' => '{_locale}/usuario/{id}/documentos', 'fr' => '{_locale}/utilisateur/{id}/documentation'], name: 'user_add_documents')]
    public function uploadDocument(
        User $user,
        Request $request,
        UploadHelper $uploadHelper,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        $locale = 'es',
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
        $filename = $uploadHelper->uploadDocument($uploadedDocument);

        $document = new Document($user);
        $document->setFilename($filename);
        $document->setOriginalFilename($uploadedDocument->getClientOriginalName() ?? $filename);
        $document->setMimeType($uploadedDocument->getMimeType() ?? 'application/octet-stream');

        $entityManager->persist($document);
        $entityManager->flush();

        return $this->json(
            $document,
            201,
            [],
            [
                'groups' => ['main'],
            ]
        );
    }

    #[Route(path: '/user/settings/{id}/document/download', name: 'download_document', methods: ['GET'])]
    public function downloadDocument(
        Document $document,
        UploadHelper $uploadHelper
    ) {
        $user = $document->getUser();

        $this->denyAccessUnlessGranted('ROLE_USER', $user);

        $response = new StreamedResponse(function () use ($document, $uploadHelper) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $uploadHelper->readStream($document->getFilePath(), false);

            stream_copy_to_stream($fileStream, $outputStream);
        });

        $response->headers->set('Content-Type', $document->getMimeType());

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $document->getOriginalFilename()
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @IsGranted("ROLE_USER", subject="user")
     */
    #[Route(path: '/user/settings/{id}/documents', methods: 'GET', name: 'frontend_user_list_documents')]
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

    #[Route(path: '/user/reservation/{reservation}/documents', options: ['expose' => true], methods: 'GET', name: 'frontend_reservation_list_documents')]
    public function getReservationDocuments(
        Reservation $reservation,
        ReservationDataRepository $reservationDataRepository
    ) {
        $reservationData = $reservationDataRepository->findOneBy(
            [
                'reservation' => $reservation,
                'User' => $this->getUser(),
            ]
        );

        /* $documents = [];
        $i = 0;
        foreach($reservationData as $reservationDataElement){
            $documents[$i] = $reservationDataElement->getDocuments();
            $i++;
        } */

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

    #[Route(path: '/user/documents/delete/{document}', name: 'frontend_user_delete_document', options: ['expose' => true], methods: ['DELETE', 'POST'])]
    public function FrontendUserDeleteDocument(
        Request $request,
        Document $document,
        UploadHelper $uploadHelper,
        TravellersRepository $travellersRepository,
        EntityManagerInterface $entityManager,
        DocumentRepository $documentRepository,
        ReservationRepository $reservationRepository
    ) {
        $user = $this->getUser();
        $type = $document->getDoctype();
        $reservationId = $request->request->get('reservationId');
        $entityManager->remove($document);
        $entityManager->flush();
        $uploadHelper->deleteFile($document->getFilePath(), false);
        $reservation = $reservationRepository->find($reservationId);

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
            $dropHtmlRenderArray['traveller'] = $travellersRepository->find($travellerId);
            $listHtmldRenderArray['documents'] = $dropHtmlRenderArray['traveller']->getDocuments();
        }
        $documents = $documentRepository->getDocumentsByReservationByUser($reservation);

        $listHtmlRenderArray['documents'] = $documents;
        $listHtml = $this->renderView('user/_documents_list.html.twig', $listHtmlRenderArray);
        $dropHtml = $this->renderView('user/_dropzone_input.html.twig', $dropHtmlRenderArray);

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

    #[Route(path: ['en' => '{_locale}/user/documents', 'es' => '{_locale}/usuario/documentos', 'fr' => '{_locale}/utilisateur/documents'], name: 'frontend_user_documents')]
    public function userDocuments(
        LangRepository $langRepository,
        $_locale = null,
        ReservationRepository $reservationRepository,
        $locale = 'es'
    ) {
        $locale = $_locale ? $_locale : $locale;
        // Swith Locale Loader
        $otherLangsArray = $langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }
        $user = $this->getUser();

        $reservations = $reservationRepository->findBy(['user' => $user]);

        return $this->render('user/user_documents.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'user' => $user,
            'reservations' => $reservations,
            'documents' => $user->getDocuments(),
        ]);
    }
}
