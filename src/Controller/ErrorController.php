<?php

namespace App\Controller;

use App\Repository\LangRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    public function __construct(
        private LangRepository $langRepository
        ) {
        $this->langRepository = $langRepository;
    }

    #[Route('/error', name: 'error')]
    public function show(
        \Throwable $exception,
        string $locale = 'es',
        string $_locale = null): Response
    {
        $locale = $_locale ?: $locale;
        // OTHER LANG MENU
        $otherLangsArray = $this->langRepository->findOthers($locale);
        $urlArray = [];
        $i = 0;
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $urlArray[$i]['category'] = '';
            $urlArray[$i]['travel'] = '';
            ++$i;
        }

        return $this->render('bundles/TwigBundle/Exception/error.html.twig', [
            'langs' => $urlArray,
            'exception' => $exception,
            'locale' => $locale,
        ]);
    }
}
