<?php

namespace App\Controller;

use App\Entity\Lang;
use App\Entity\MenuTranslation;
use App\Entity\CategoryTranslation;
use App\Entity\Category;
use App\Entity\Dates;
use App\Repository\CategoryRepository;
use App\Repository\CategoryTranslationRepository;
use App\Repository\DatesRepository;
use App\Repository\LangRepository;
use App\Repository\MenuTranslationRepository;
use App\Repository\TextsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Service\UploadHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MainController extends AbstractController
{

    
    public function langMenu(
        Request $request,
        LangRepository $langRepository, 
        CategoryTranslationRepository $categoryTranslationRepository,
        DatesRepository $datesRepository,
        $locale ='es', 
        $slug = '', 
        $category = '')
    {
        $allAttributes = $request->attributes->all();
        $route = $request->attributes->get('_route');
        $slug = $request->attributes->get('slug');
        $category = ($request->attributes->get('category')) ? $request->attributes->get('category') : $category;
        $locale = ($request->attributes->get('_locale')) ? $request->attributes->get('_locale') : $locale;

        
        $otherLangs = $langRepository->findOthers($locale);
        
        $thisLang = $langRepository->findOneBy([
            'iso_code' => $locale
        ]);
        /** @var $router \Symfony\Component\Routing\Router */
        $router = $this->container->get('router');
        $links = [];
        $i = 0;
        foreach($otherLangs as $otherLang){            
            if($category!= '') {
        
                $categoryTranslation = $categoryTranslationRepository->findOneBy( [
                    'slug'=> $category,
                    'lang'=> $thisLang->getId()
                ]);
               
                $otherCategoryTranslation = $categoryTranslationRepository->findOtherCategoryTranslations($otherLang, $categoryTranslation->getCategory());
            

                $links[$i]['url'] = $router->generate($allAttributes['_route'],[
                    'slug' => $slug,
                    'category' => $otherCategoryTranslation->getSlug(),
                    '_locale' => $otherLang->getIsoCode()
                ]);
            } else if( 
                $route == "reservation" || 
                $route == "reservation_data"){
                    
                $dateId = ($request->attributes->get('date'))?$request->attributes->get('date'):$request->request->get('date');
                $date = $datesRepository->find($dateId);
                $links[$i]['url'] = $router->generate($allAttributes['_route'],[
                    'date' => $date->getId(),
                    '_locale' => $otherLang->getIsoCode()
                ]);
            } else if(
                $route == "reservation_payment" ||
                $route == "success_url"
            ){ 
                $reservation = $request->attributes->get('reservation');
                $links[$i]['url'] = $router->generate($allAttributes['_route'],[
                    'reservation' => $reservation->getId(),
                    '_locale' => $otherLang->getIsoCode()
                ]);
            }else {
                $links[$i]['url'] = $router->generate($allAttributes['_route'],[
                    'slug' => $slug,
                    '_locale' => $otherLang->getIsoCode()
                ]);
            }
            $links[$i]['name'] = $otherLang->getName();
            $i++;
        }
        
        return $links;
        
    }

    public function showMenu(
                        $locale = 'es', 
                        Request $request,
                        LangRepository $langRepository,
                        MenuTranslationRepository $menuTranslationRepository)
    {
        if($request->getSession()->get('_locale')){
            $request->getSession()->set('_locale', $locale);
        } else {
            $request->getSession()->set('_locale', $request->request->get('_locale'));
        }
        
        $lang = $langRepository->findBy(['iso_code'=>$locale]);
        $menuTranslationResult = $menuTranslationRepository->findBy([
            'lang' => $lang
        ]);
        
        return($menuTranslationResult);

    }
    
}
