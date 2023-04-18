<?php

namespace App\Controller;

use App\Entity\Invoices;
use App\Repository\InvoicesRepository;
use App\Repository\LangRepository;
use App\Service\breadcrumbsHelper;
use App\Service\languageMenuHelper;
use App\Service\UploadHelper;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class UserFrontendReservationInvoicesController extends AbstractController
{

    public function __construct(
        private TranslatorInterface $translatorInterface,
        private InvoicesRepository $invoicesRepository,
        private LangRepository $langRepository,
        private UploadHelper $uploadHelper,
        private breadcrumbsHelper $breadcrumbsHelper,
        private languageMenuHelper $languageMenuHelper)
    {
    }

    #[Route(
        path: [
            'en' => '{_locale}/user/invoices',
            'es' => '{_locale}/usuario/facturas',
            'fr' => '{_locale}/utilisateur/factures'],
        name: 'frontend_user_invoices')]
    public function userInvoices(
        Request $request,
        PaginatorInterface $paginator,
        string $_locale = null,
        string $locale = 'es'
    ) {
        $locale = $_locale ?: $locale;
        $urlArray = $this->languageMenuHelper->basicLanguageMenu($locale);

        // BREADCRUMBS
        $this->breadcrumbsHelper->frontendUserInvoicesBreadcrumbs($locale);


        $user = $this->getUser();

        $query = $this->invoicesRepository->findByUser($user);

        $invoices = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render('user/user_invoices.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'invoices' => $invoices,
        ]);
    }

    #[Route(
        path: '/user/download/invoice/{invoice}',
        name: 'user-download-invoice')]
    public function downloadInvoice(
                        Invoices $invoice): Response
    {

        $uploadHelper = $this->uploadHelper;
        $response = new StreamedResponse(function () use ($invoice, $uploadHelper) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $this->uploadHelper->readStream($invoice->getFilePath(), false);
            stream_copy_to_stream($fileStream, $outputStream);
        });

        $response->headers->set('Content-Type', 'application/pdf');

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $invoice->getOriginalFilename()
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}
