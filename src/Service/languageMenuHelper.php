<?php 

namespace App\Service;

use App\Entity\CategoryTranslation;
use App\Entity\Dates;
use App\Entity\PageTranslation;
use App\Entity\Reservation;
use App\Entity\TravelTranslation;
use App\Repository\CategoryTranslationRepository;
use App\Repository\LangRepository;
use App\Repository\PageTranslationRepository;
use App\Repository\TravelTranslationRepository;

class languageMenuHelper {
  public function __construct(
    private LangRepository $langRepository,
    private PageTranslationRepository $pageTranslationRepository, 
    private TravelTranslationRepository $travelTranslationRepository,
    private CategoryTranslationRepository $categoryTranslationRepository){
  }

  public function basicLanguageMenu($locale){
    $otherLangsArray = $this->langRepository->findOthers($locale);
    $i = 0;
    $urlArray = [];
    foreach ($otherLangsArray as $otherLangArray) {
      $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
      $urlArray[$i]['lang_name'] = $otherLangArray->getName();
      ++$i;
    }
    return $urlArray;
  }
  public function indexLanguageMenu(string $locale) :array {
    $otherLangsArray = $this->langRepository->findOthers($locale);
    $urlArray = $this->basicLanguageMenu($locale);
    $i = 0;
    foreach ($otherLangsArray as $otherLangArray) {
        $urlArray[$i]['category'] = '';
        $urlArray[$i]['travel'] = '';
        ++$i;
    }
    return $urlArray;
  }

  public function travelLanguageMenu($locale, $travel) :array {
    $travelTranslation = $this->travelTranslationRepository->findOneBy(
      ['url' => $travel]
    );
    $otherLangsArray = $this->langRepository->findOthers($locale);
        $i = 0;
        $urlArray = $this->basicLanguageMenu($locale);
        foreach ($otherLangsArray as $otherLangArray) {
            $otherCategoryTranslation = $this->categoryTranslationRepository->findOneBy(
                [
                    'category' => $travelTranslation->getTravel()->getCategory(),
                    'lang' => $otherLangArray,
                ]
            );
            $urlArray[$i]['category'] = $otherCategoryTranslation->getSlug();
            $otherTravelTranslation = $this->travelTranslationRepository->findOneBy(
                [
                    'travel' => $travelTranslation->getTravel(),
                    'lang' => $otherLangArray,
                ]
            );
            $urlArray[$i]['travel'] = $otherTravelTranslation->getUrl();
            ++$i;
        }
        // End Locale switcher
        return $urlArray;
  }

  public function categoryLanguageMenu($locale, $category) :array
  {
    $otherLangsArray = $this->langRepository->findOthers($locale);
    $i = 0;
    $urlArray = $this->basicLanguageMenu($locale);
      foreach ($otherLangsArray as $otherLangArray) {
        $otherCategoryTranslation = $this->categoryTranslationRepository->findOneBy(
                [
                    'category' => $category->getId(),
                    'lang' => $otherLangArray->getId(),
                ]
            );

            $urlArray[$i]['category'] = $otherCategoryTranslation->getSlug();
            ++$i;
        }
      return $urlArray;
  }


  public function slugLanguageMenu(
    string $locale, 
    PageTranslation $pageTranslation) :array{
    $otherLangsArray = $this->langRepository->findOthers($locale);
        $i = 0;
        $urlArray = $this->basicLanguageMenu($locale);
        foreach ($otherLangsArray as $otherLangArray) {
            $otherPageTranslation = $this->pageTranslationRepository->findOneBy(
                [
                    'Page' => $pageTranslation->getPage(),
                    'lang' => $otherLangArray,
                ]
            );
            $urlArray[$i]['page'] = $otherPageTranslation->getSlug();
            ++$i;
        }
    return $urlArray;
  }
  public function reservationMenuLanguage(
    string $locale, 
    TravelTranslation $travelTranslation, 
    CategoryTranslation $categoryTranslation,
    Dates $date) :array 
  {
  
    $otherLangsArray = $this->langRepository->findOthers($locale);

    $i = 0;
    $urlArray = $this->basicLanguageMenu($locale);
    foreach ($otherLangsArray as $otherLangArray) {
      $otherCategoryTranslation = $this->categoryTranslationRepository->findOneBy(
                [
                  'category' => $categoryTranslation->getCategory(),
                  'lang' => $otherLangArray,
                ]);
      $urlArray[$i]['category'] = $otherCategoryTranslation->getSlug();
      $otherTravelTranslation = $this->travelTranslationRepository->findOneBy(
          [
              'travel' => $travelTranslation->getTravel(),
              'lang' => $otherLangArray,
          ]);
      $urlArray[$i]['travel'] = $otherTravelTranslation->getUrl();
      $urlArray[$i]['date'] = $date->getDebut()->format('Ymd');
      ++$i;
    }
    return $urlArray;
  }

  public function reservationPaymentMenuLanguage(
    string $locale, 
    Reservation $reservation):array
  {
    $otherLangsArray = $this->langRepository->findOthers($locale);
    $urlArray = $this->basicLanguageMenu($locale);
    $i = 0;
    foreach ($otherLangsArray as $otherLangArray) {
        $urlArray[$i]['reservation'] = $reservation->getId();
        ++$i;
    }
    return $urlArray;
  }

  public function paymentSuccessReservationMenuLanguage(
      string $locale, 
      Reservation $reservation) {
    $otherLangsArray = $this->langRepository->findOthers($locale);
    $urlArray = $this->basicLanguageMenu($locale);
    $i = 0;
    foreach ($otherLangsArray as $otherLangArray) {
        $urlArray[$i]['reservation'] = $reservation->getId();
        ++$i;
    }
    return $urlArray;
  }

  public function cancelledPaymentReservationMenuLanguage(string $locale, Reservation $reservation) :array
  {
    $otherLangsArray = $this->langRepository->findOthers($locale);
    $urlArray = $this->basicLanguageMenu($locale);
    $i = 0;
    foreach ($otherLangsArray as $otherLangArray) {
        $urlArray[$i]['reservation'] = $reservation->getId();
        ++$i;
    }
    return $urlArray;
  }

  public function userFrontendReservationDataMenuLanguage($locale, $reservation):array
  {
      $otherLangsArray = $this->langRepository->findOthers($locale);
      $i = 0;
      $urlArray = $this->basicLanguageMenu($locale);
      foreach ($otherLangsArray as $otherLangArray) {
          $urlArray[$i]['reservation'] = $reservation;
          ++$i;  
      }
      return $urlArray;
  }

}