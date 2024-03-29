<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\CategoryTranslationRepository;
use App\Repository\ContinentsRepository;
use App\Repository\DatesRepository;
use App\Repository\LangRepository;
use App\Repository\MenuRepository;
use App\Repository\MenuTranslationRepository;
use App\Repository\PagesRepository;
use App\Repository\PageTranslationRepository;
use App\Repository\PopupsRepository;
use App\Repository\TextsRepository;
use App\Repository\TravelRepository;
use App\Repository\TravelTranslationRepository;
use App\Service\breadcrumbsHelper;
use App\Service\languageMenuHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrontendController extends AbstractController
{
    public function __construct(
        private TravelRepository $travelRepository,
        private DatesRepository $datesRepository,
        private CategoryRepository $categoryRepository,
        private CategoryTranslationRepository $categoryTranslationRepository,
        private TextsRepository $textsRepository,
        private LangRepository $langRepository,
        private MenuTranslationRepository $menuTranslationRepository,
        private PageTranslationRepository $pageTranslationRepository,
        private TravelTranslationRepository $travelTranslationRepository,
        private ContinentsRepository $continentsRepository,
        private MenuRepository $menuRepository,
        private PagesRepository $pagesRepository,
        private breadcrumbsHelper $breadcrumbsHelper,
        private languageMenuHelper $languageMenuHelper,
        private popupsRepository $popupsRepository
    ) {
    }

    #[Route(
        path: '/',
        name: 'index')]
    #[Route(
        path: '/{_locale}',
        name: 'index',
        requirements: ['_locale' => '^[a-z]{2}$'])]
    public function index(
        string $_locale = 'es'
    ): Response {
        $urlArray = $this->languageMenuHelper->indexLanguageMenu($_locale);

        $categories = $this->categoryRepository->findCategoriesForIndex(
            $this->continentsRepository->findOneBy(
                ['code' => 'as']
            ),
            $_locale);

        $otherCategories = $this->categoryRepository->findOtherCategoriesForIndex(
            $this->continentsRepository->findOneBy(
                ['code' => 'as']
            ),
            $_locale);


        $calendarDates = [];
        $j = 0;
        foreach ($this->datesRepository->getDatesByYear() as $year) {
            $calendarDates[$j]['year'] = $year;
            $calendarDates[$j]['year']['months'] = $this->datesRepository->getMonthedDates($year);
            $k = 0;
            foreach ($calendarDates[$j]['year']['months'] as $month) {
                $calendarDates[$j]['year']['months'][$k] = $month;
                $calendarDates[$j]['year']['months'][$k]['dates'] = $this->datesRepository->showDatesByMonth($month, $year);
                ++$k;
            }
            ++$j;
        }

        // End of Dates' right block

        // Url slug term definitions.
        $destinations = [
            'en' => 'trips',
            'es' => 'destinos',
            'fr' => 'destinations',
        ];

        return $this->render('index/index.html.twig', [
            'categories' => $categories,
            'otherCategories' => $otherCategories,
            'locale' => $_locale,
            'langs' => $urlArray,
            'destinations' => $destinations[$_locale],
            'dates' => $calendarDates,
        ]);
    }

    #[Route(
        path: [
            'en' => '{_locale}/trips/{category}/{travel}/',
            'es' => '{_locale}/destinos/{category}/{travel}/',
            'fr' => '{_locale}/destinations/{category}/{travel}/'],
        name: 'destination',
        requirements: ['_locale' => '^[a-z]{2}$'])]
    public function Destination(
        Request $request,
        string $_locale = 'es'
    ): Response {
        $category = $request->attributes->get('category');
        $categoryTranslation = $this->categoryTranslationRepository->findOneBy(
            ['slug' => $category]
        );
        $slug = $request->attributes->get('travel');

        $urlArray = $this->languageMenuHelper->travelLanguageMenu($_locale, $slug);
        $travel = $this->travelRepository->showTravelFromTranslationSlug($slug);

        $travelTranslation = $this->travelTranslationRepository->findOneBy(
            ['url' => $slug]
          );


        $travel = $this->travelRepository->showTravelFromTranslationSlug($slug);
        $travelObject = $this->travelRepository->find($travel['id']);

        // BREADCRUMBS //
        // 2. Get the CategoryTranslation for that route
        $categoryTranslation2 = $this->categoryTranslationRepository->findOneBy([
            'title' => $categoryTranslation->getTitle(),
            'lang' => $this->langRepository->findOneBy(['iso_code' => $_locale]),
        ]);
        $this->breadcrumbsHelper->destinationsByTravelDestinationBreadcrumbs(
            $_locale,
            $categoryTranslation,
            $categoryTranslation2,
            $travelTranslation);
        // END BREADCRUMBS //

        $dates = $this->datesRepository->showNextDates($travel);

        return $this->render('frontend/destination_single.html.twig', [
            'locale' => $_locale,
            'langs' => $urlArray,
            'travel' => $travel,
            'travelObject' => $travelObject,
            'dates' => $dates,
            'category' => $category,
        ]);
    }

    #[Route(
        path: [
            'en' => '{_locale}/trips/{category}/',
            'es' => '{_locale}/destinos/{category}/',
            'fr' => '{_locale}/destinations/{category}/'],
        name: 'destinations-by-category',
        requirements: ['_locale' => '^[a-z]{2}$'])]
    public function DestinationsByCategory(
        Request $request,
        string $_locale = 'es'
    ): Response {
        $categorySlug = $request->attributes->get('category');
        $categoryTranslation = $this->categoryTranslationRepository->findOneBy(['slug' => $categorySlug]);

        $category = $categoryTranslation->getCategory();
        $urlArray = $this->languageMenuHelper->categoryLanguageMenu($_locale, $category);
        $otherLangsArray = $this->langRepository->findOthers($_locale);

        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();

            $otherCategoryTranslation = $this->categoryTranslationRepository->findOneBy(
                [
                    'category' => $category->getId(),
                    'lang' => $otherLangArray->getId(),
                ]
            );

            $urlArray[$i]['category'] = $otherCategoryTranslation->getSlug();
            ++$i;
        }

        // Get Content
        // 1. Get the lang object for $locale
        $lang = $this->langRepository->findOneBy(['iso_code' => $_locale]);
        // 2. Get the CategoryTranslation for that route
        $categoryTranslation2 = $this->categoryTranslationRepository->findOneBy([
            'title' => $categoryTranslation->getTitle(),
            'lang' => $lang,
        ]);

        $this->breadcrumbsHelper->destinationsByCategoryBreadcrumbs(
            $_locale,
            $categoryTranslation,
            $categoryTranslation2);

        // 3. Get the travels for that category
        $travels = $this->travelRepository
            ->showByCategory(
                $categoryTranslation->getCategory()->getId(),
                $_locale
            );

        $items = [];
        $i = 0;
        foreach ($travels as $travel) {
            $items[$i] = $travel;
            $items[$i]['dates'] = $this->datesRepository->showNextDates($travel);
            ++$i;
        }
        // dd($urlArray);

        return $this->render('frontend/destination_category.html.twig', [
            'locale' => $_locale,
            'langs' => $urlArray,
            'travels' => $items,
            'category' => $categoryTranslation,
        ]);
    }

    #[Route(
        path: [
            'en' => '{_locale}/trips/',
            'es' => '{_locale}/destinos/',
            'fr' => '{_locale}/destinations/'],
        name: 'destinations',
        requirements: ['_locale' => '^[a-z]{2}$'])]
    public function Destinations(
        Request $request,
        string $_locale = 'es'
    ): Response {

        $route = $request->attributes->get('_route');
        $this->breadcrumbsHelper->destinationsBreadcrumbs();
        $urlArray = $this->languageMenuHelper->basicLanguageMenu($_locale);

        // Get the category for that translation
        $continent = $this->continentsRepository->findOneBy(['code' => 'as']);

        $categories = $this->categoryRepository->findCategoriesForIndex($continent, $_locale);
        $otherDestinations = $this->categoryRepository->findOtherCategoriesForIndex($continent, $_locale);

        $slug = $this->menuTranslationRepository->findCorrespondingRoute($route, $_locale);

        return $this->render('destinations/index.html.twig', [
            'locale' => $_locale,
            'langs' => $urlArray,
            'categories' => $categories,
            'route' => $slug,
            'otherDestinations' => $otherDestinations,
        ]);
    }

    #[Route(
        path: [
            'en' => '{_locale}/calendar/',
            'es' => '{_locale}/calendario/',
            'fr' => '{_locale}/calendrier/'],
        name: 'calendar',
        requirements: ['_locale' => '^[a-z]{2}$'])]
    public function Calendar(
        Request $request,
        string $_locale = 'es'
    ): Response {
        $this->breadcrumbsHelper->calendarBreadcrumbs();

        $slug = $request->attributes->get('slug');

        $urlArray = $this->languageMenuHelper->basicLanguageMenu($_locale);

        // 1. get dates only by month/year
        // return months array
        $years = $this->datesRepository->getDatesByYear();

        $calendarDates = [];
        $j = 0;
        foreach ($years as $year) {
            $calendarDates[$j]['year'] = $year;
            $calendarDates[$j]['year']['months'] = $this->datesRepository->getMonthedDates($year);
            $k = 0;
            foreach ($calendarDates[$j]['year']['months'] as $month) {
                $calendarDates[$j]['year']['months'][$k]['month'] = $month;
                $calendarDates[$j]['year']['months'][$k]['dates'] = $this->datesRepository->showDatesByMonth($month, $year);
                
                ++$k;
            }
            ++$j;
        }
        dump($calendarDates);
        return $this->render('calendar/index.html.twig', [
            'locale' => $_locale,
            'langs' => $urlArray,
            'dates' => $calendarDates,
            'route' => $slug,
        ]);
    }

    #[Route(
        path: '{_locale}/{slug}/',
        name: 'main-content',
        requirements: ['_locale' => '^[a-z]{2}$']
        )]
    public function main(
        string $slug,
        string $_locale = 'es'
    ): Response {

        $pageTranslation = $this->pageTranslationRepository->findOneBy(['slug' => $slug]);
        $page = $pageTranslation->getPage();

        $this->breadcrumbsHelper->mainBreadcrumbs($_locale, $page);
        $urlArray = $this->languageMenuHelper->slugLanguageMenu($_locale, $pageTranslation);

        $lang = $this->langRepository->findOneBy(['iso_code' => $_locale]);

        $menuTranslation = $this->menuTranslationRepository->findOneBy([
            'slug' => $slug,
            'lang' => $lang,
        ]);
        $menu = $this->menuRepository->find($menuTranslation->getMenu()->getId());

        $pageTranslation = $this->pageTranslationRepository->findOneBy([
            'Page' => $menu->getPage()->getId(),
            'lang' => $lang,
        ]);

        return $this->render('presentation/index.html.twig', [
            'locale' => $_locale,
            'langs' => $urlArray,
            'contents' => $pageTranslation,
            'page' => $slug,
            'slug' => $pageTranslation->getSlug(),
        ]);
    }

    #[Route(
        path: '/ajax/show-popup',
        options: ['expose' => true],
        name: 'ajax-show-popup')]
    public function showPopup()
    {
        $popup = $this->popupsRepository->showPopup('es');
        if ($popup != null) {
            $html = $this->renderView('index/popup.html.twig', [
                'popup' => $popup,
            ]);

            return $this->json(['html' => $html], 200);
        } else {
            return $this->json(['html' => null], 401);
        }
    }
}
