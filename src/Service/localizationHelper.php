<?php

namespace App\Service;

use App\Entity\Popups;
use App\Repository\CategoryRepository;
use App\Repository\CategoryTranslationRepository;
use App\Repository\LangRepository;
use App\Repository\OptionsRepository;
use App\Repository\OptionsTranslationsRepository;
use App\Repository\PagesRepository;
use App\Repository\PageTranslationRepository;
use App\Repository\PopupsTranslationRepository;
use App\Repository\ReservationOptionsRepository;
use App\Repository\TravelRepository;
use App\Repository\TravelTranslationRepository;
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
        private TravelRepository $travelRepository,
        private PopupsTranslationRepository $popupsTranslationRepository
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
            'lang' => $lang, ]);
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

        $optionTranslation = $this->optionsTranslationsRepository->findOneBy(
            [
                'option' => $option,
                'lang' => $lang,
            ]);

        if ($optionTranslation === null) {
            return null;
        } else {
            return $optionTranslation->getTitle();
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

    public function renderPopupTitle(Popups $popup, string $locale): mixed
    {
        $lang = $this->_getLang($locale);
        $popupObject = $this->popupsTranslationRepository->findOneBy(
            [
                'popup' => $popup,
                'lang' => $lang,
            ]);

        return $popupObject == null ? null : $popupObject->getTitle();
    }

    public function renderPopupContent(Popups $popup, string $locale): mixed
    {
        $lang = $this->_getLang($locale);
        $popupObject = $this->popupsTranslationRepository->findOneBy(
            [
                'popup' => $popup,
                'lang' => $lang,
            ]);

        return $popupObject == null ? null : $popupObject->getContent();
    }
}
