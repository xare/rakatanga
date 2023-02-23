<?php

namespace App\Service;

use App\Entity\Document;
use App\Entity\Invoices;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class downloadHelper {

  public function __construct(private UploadHelper $uploadHelper) {

  }

  public function downloadStreamInvoice(Invoices $invoice) {
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

  public function downloadStreamDocument(Document $document) {
    $uploadHelper = $this->uploadHelper;
    $response = new StreamedResponse(function () use ($document, $uploadHelper) {
        $outputStream = fopen('php://output', 'wb');
        $fileStream = $this->uploadHelper->readStream($document->getFilePath(), false);
        stream_copy_to_stream($fileStream, $outputStream);
    });
    $response->headers->set('Content-Type', 'application/pdf');
    $disposition = HeaderUtils::makeDisposition(
        HeaderUtils::DISPOSITION_ATTACHMENT,
        $document->getOriginalFilename()
    );

    $response->headers->set('Content-Disposition', $disposition);

    return $response;
}
}