<?php 

namespace App\Service;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Entity\Lang;
use App\Entity\Options;
use App\Entity\OptionsTranslations;
use App\Entity\Pages;
use App\Entity\PageTranslation;
use App\Entity\Travel;
use App\Entity\TravelTranslation;
use App\Repository\OptionsRepository;
use App\Repository\ReservationOptionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class localizationHelper
{
    private $em;
    private $optionsRepository;
    private $translator;
    private $reservationOptionsRepository;

    public function __construct( 
        EntityManagerInterface $em,
        OptionsRepository $optionsRepository,
        ReservationOptionsRepository $reservationOptionsRepository,
        TranslatorInterface $translator
         )
    {
        $this->em = $em;
        $this->optionsRepository = $optionsRepository;
        $this->reservationOptionsRepository = $reservationOptionsRepository;
        $this->translator = $translator;
    }

    public function renderTravelString($id, $locale)
    {
        $travel = $this->em->getRepository(Travel::class)->find($id);
        $lang = $this->_getLang($locale);
        $travelObject = $this->em
        ->getRepository(TravelTranslation::class)
        ->findOneBy(['travel'=>$travel,'lang'=>$lang]); 
        
        if (null === $travelObject){
            return '';
        } else {
            return $travelObject->getTitle();
        }
    }
    public function renderTravelUrl($id, $locale)
    {
        $travel = $this->em->getRepository(Travel::class)->find($id);
        $lang = $this->_getLang($locale);
        $travelObject = $this->em
        ->getRepository(TravelTranslation::class)
        ->findOneBy(['travel'=>$travel,'lang'=>$lang]); 
        if (null === $travelObject){
            return '';
        } else {
            return $travelObject->getUrl();
        }
    }  
    public function renderOptionString($id, $locale)
    {

        $option = $this->optionsRepository->find($id);

        $lang = $this->_getLang($locale);

        $Object = $this->em
        ->getRepository(OptionsTranslations::class)
        ->findOneBy(
            [
                'options'=>$option,
                'lang'=>$lang
            ]); 
        
        if ($Object === null){
            return null;
        } else {
            return $Object->getTitle();
        }       
    } 

    public function renderOptionIntro($id, $locale)
    {

        $option = $this->optionsRepository->find($id);

        $lang = $this->_getLang($locale);

        $Object = $this->em
        ->getRepository(OptionsTranslations::class)
        ->findOneBy(
            [
                'options'=>$option,
                'lang'=>$lang
            ]); 
        
        if ($Object === null){
            return null;
        } else {
            return $Object->getIntro();
        }       
    } 

    public function renderCategoryString($id,$locale){
        $category = $this->em->getRepository(Category::class)->find($id);
        $lang = $this->_getLang($locale);
        $Object = $this->em
        ->getRepository(CategoryTranslation::class)
        ->findOneBy(
            [
                'category'=>$category,
                'lang'=>$lang
            ]); 
        if ($Object === null){
            return null;
        } else {
            return $Object->getTitle();
        }    
    }

    public function renderPageString($id,$locale){
        $page = $this->em->getRepository(Pages::class)->find($id);
        $lang = $this->_getLang($locale);
        $Object = $this->em
        ->getRepository(PageTranslation::class)
        ->findOneBy(
            [
                'Page'=>$page,
                'lang'=>$lang
            ]); 
        if ($Object === null){
            return null;
        } else {
            return $Object->getTitle();
        }    
    }

    private function _getLang($locale){
        return $this->em->getRepository(Lang::class)->findBy(
            ['iso_code' => $locale]
        );
    }
}