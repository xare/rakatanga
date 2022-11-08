<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\MainController;
use App\Repository\LangRepository;

class FaqController extends MainController
{
   /**
     * @Route({
     *      "en": "{_locale}/faq",
     *      "es": "{_locale}/faq",
     *      "fr": "{_locale}/faq"
     *      }, name="faq")
     */
    public function index(
        Request $request,
        LangRepository $langRepository
        ): Response
    {
        $route = $request->attributes->get('_route');
        $locale = $request->attributes->get('_locale');

        $otherLangsArray = $langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach($otherLangsArray as $otherLangArray){
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $i++;
        }

        return $this->render('faq/index.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,

        ]);
    }
}
