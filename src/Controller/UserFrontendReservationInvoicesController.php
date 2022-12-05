<?php

namespace App\Controller;

use App\Entity\Invoices;
use App\Repository\InvoicesRepository;
use App\Repository\LangRepository;
use App\Repository\ReservationRepository;
use App\Service\UploadHelper;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class UserFrontendReservationInvoicesController extends AbstractController
{
    private $translatorInterface;

    public function __construct(TranslatorInterface $translatorInterface)
    {
        $this->translatorInterface = $translatorInterface;
    }

    #[Route(path: ['en' => '{_locale}/user/invoices', 'es' => '{_locale}/usuario/facturas', 'fr' => '{_locale}/utilisateur/factures'], name: 'frontend_user_invoices')]
    public function userInvoices(
        Request $request,
        ReservationRepository $reservationsRepository,
        InvoicesRepository $invoicesRepository,
        LangRepository $langRepository,
        PaginatorInterface $paginator,
        Breadcrumbs $breadcrumbs,
        string $locale = 'es'
    ) {
        $locale = ($request->attributes->get('_locale')) ? $request->attributes->get('_locale') : $locale;
        // Swith Locale Loader
        $otherLangsArray = $langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }
        // En switch locale loader
        // BREADCRUMBS
        $breadcrumbs->addRouteItem(
            $this->translatorInterface->trans('Usuario'),
            'frontend_user',
            [
                '_locale' => $locale,
            ]
        );
        $breadcrumbs->addRouteItem(
            $this->translatorInterface->trans('Facturas del usuario'),
            'frontend_user_invoices',
            [
                '_locale' => $locale,
            ]
        );
        $breadcrumbs->prependRouteItem(
            $this
                ->translatorInterface
                ->trans('Inicio'),
            'index'
        );
        // END BREADCRUMBS

        $user = $this->getUser();

        $query = $invoicesRepository->findByUser($user);

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

    #[Route(path: '/user/download/invoice/{invoice}', name: 'user-download-invoice')]
    public function downloadInvoice(
        Invoices $invoice,
        UploadHelper $uploadHelper
    ): Response {
        $response = new StreamedResponse(function () use ($invoice, $uploadHelper) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $uploadHelper->readStream($invoice->getFilePath(), false);
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
