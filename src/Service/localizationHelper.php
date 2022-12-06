<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Entity\Lang;
use App\Entity\OptionsTranslations;
use App\Entity\Pages;
use App\Entity\PageTranslation;
use App\Entity\Travel;
use App\Entity\TravelTranslation;
use App\Repository\CategoryRepository;
use App\Repository\CategoryTranslationRepository;
use App\Repository\LangRepository;
use App\Repository\OptionsRepository;
use App\Repository\OptionsTranslationsRepository;
use App\Repository\PagesRepository;
use App\Repository\PageTranslationRepository;
use App\Repository\ReservationOptionsRepository;
use App\Repository\TravelRepository;
use App\Repository\TravelTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class localizationHelper
{

    public function __construct(
        private CategoryRepository $categoryRepository,
        private CategoryTranslationRepository $categoryTranslationRepository,
        private LangRepository $langRepository,
        private OptionsRepository $optionsRepository,
        private OptionsTranslationsRepository $optionsTranslationsRepository,
        private PagesRepository $pagesRepository,
        private PageTranslationRepository $pageTranslationRepository,
        private ReservationOptionsRepository $reservationOptionsRepository,
        private TranslatorInterface $translator,
        private TravelTranslationRepository $travelTranslationRepository,
        private TravelRepository $travelRepository
         ) {
    }

    public function renderTravelString($id, $locale)
    {
        $travel = $this->travelTranslationRepository->find($id);
        $lang = $this->_getLang($locale);
        $travelObject = $this->travelTranslationRepository->findOneBy(['travel' => $travel, 'lang' => $lang]);

        if (null === $travelObject) {
            return '';
        } else {
            return $travelObject->getTitle();
        }
    }

    public function renderTravelUrl($id, $locale)
    {
        $travel = $this->travelRepository->find($id);
        $lang = $this->_getLang($locale);
        $travelObject = $this->travelTranslationRepository->findOneBy(
                            ['travel' => $travel, 
                            'lang' => $lang]);
        if (null === $travelObject) {
            return '';
        } else {
            return $travelObject->getUrl();
        }
    }

    public function renderOptionString($id, $locale)
    {
        $option = $this->optionsRepository->find($id);

        $lang = $this->_getLang($locale);

        $Object = $this->optionsTranslationsRepository->findOneBy(
            [
                'options' => $option,
                'lang' => $lang,
            ]);

        if ($Object === null) {
            return null;
        } else {
            return $Object->getTitle();
        }
    }

    public function renderOptionIntro($id, $locale)
    {
        $option = $this->optionsRepository->find($id);
        $lang = $this->_getLang($locale);
        $Object = $this->optionsTranslationsRepository->findOneBy(
            [
                'options' => $option,
                'lang' => $lang,
            ]);
        if ($Object === null) {
            return null;
        } else {
            return $Object->getIntro();
        }
    }

    public function renderOptionInfodoc($id, $locale)
    {
        $option = $this->optionsRepository->find($id);
        $lang = $this->_getLang($locale);
        $Object = $this->optionsTranslationsRepository->findOneBy(
            [
                'options' => $option,
                'lang' => $lang,
            ]);
        if ($Object->getInfodocs() === null) {
            return null;
        } else {
            return $Object->getInfodocs();
        }
    }

    public function renderCategoryString($id, $locale)
    {
        $category = $this->categoryRepository->find($id);

        $lang = $this->_getLang($locale);
        $Object = $this->categoryTranslationRepository->findOneBy(
            [
                'category' => $category,
                'lang' => $lang,
            ]);
        if ($Object === null) {
            return null;
        } else {
            return $Object->getTitle();
        }
    }

    public function renderPageString($id, $locale)
    {
        $page = $this->pagesRepository->find($id);
        $lang = $this->_getLang($locale);
        $Object = $this->pageTranslationRepository->findOneBy(
            [
                'Page' => $page,
                'lang' => $lang,
            ]);
        if ($Object === null) {
            return null;
        } else {
            return $Object->getTitle();
        }
    }

    private function _getLang($locale)
    {
        return $this->langRepository->findBy(
            ['iso_code' => $locale]
        );
    }
}
