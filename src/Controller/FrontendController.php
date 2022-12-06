<?php

namespace App\Controller;

use App\Entity\Lang;
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

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
        private ContinentsRepository $continentsRepository,
        private MenuRepository $menuRepository,
        private TranslatorInterface $translatorInterface,
        private PagesRepository $pagesRepository,
        private breadcrumbsHelper $breadcrumbsHelper
    ) {
        $this->travelRepository = $travelRepository;
        $this->categoryTranslationRepository = $categoryTranslationRepository;
        $this->datesRepository = $datesRepository;
        $this->categoryRepository = $categoryRepository;
        $this->textsRepository = $textsRepository;
        $this->langRepository = $langRepository;
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->continentsRepository = $continentsRepository;
        $this->menuRepository = $menuRepository;
        $this->translatorInterface = $translatorInterface;
        $this->pageTranslationRepository = $pageTranslationRepository;
        $this->pagesRepository = $pagesRepository;
        $this->breadcrumbsHelper = $breadcrumbsHelper;
    }

    #[Route(path: '/', name: 'index')]
    #[Route(path: ['en' => '{_locale}/', 'es' => '{_locale}/', 'fr' => '{_locale}/'], name: 'index')]
    public function index(
        string $locale = 'es',
        string $_locale = null
    ): Response {
        $locale = $_locale ? $_locale : $locale;
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

        $travels = $this->travelRepository->findTravelsForIndex($locale);
        $continent = $this->continentsRepository->findOneBy(
            ['code' => 'as']
        );

        $categories = $this->categoryRepository->findCategoriesForIndex($continent, $locale );
        $otherCategories = $this->categoryRepository->findOtherCategoriesForIndex($continent, $locale);

        $years = $this->datesRepository->getDatesByYear();

        $calendarDates = [];
        $j = 0;
        foreach ($years as $year) {
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
            'locale' => $locale,
            'langs' => $urlArray,
            'destinations' => $destinations[$locale],
            'dates' => $calendarDates,
        ]);
    }

    #[Route(path: ['en' => '{_locale}/trips/{category}/{travel}/', 'es' => '{_locale}/destinos/{category}/{travel}/', 'fr' => '{_locale}/destinations/{category}/{travel}/'], name: 'destination')]
    public function Destination(
        Request $request,
        Breadcrumbs $breadcrumbs,
        CategoryTranslationRepository $categoryTranslationRepository,
        TravelTranslationRepository $travelTranslationRepository,
        LangRepository $langRepository,
        $locale = 'es',
        string $_locale = null
    ): Response {
        $locale = $_locale ? $_locale : $locale;
        $lang = $langRepository->findBy(['iso_code' => $locale]);
        $category = $request->attributes->get('category');
        $categoryTranslation = $categoryTranslationRepository->findOneBy(
            ['slug' => $category]
        );

        $travel = $request->attributes->get('travel');

        $travelTranslation = $travelTranslationRepository->findOneBy(
            ['url' => $travel]
        );

        // Locale switcher
        $otherLangsArray = $langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $otherCategoryTranslation = $categoryTranslationRepository->findOneBy(
                [
                    'category' => $categoryTranslation->getCategory(),
                    'lang' => $otherLangArray,
                ]
            );
            $urlArray[$i]['category'] = $otherCategoryTranslation->getSlug();
            $otherTravelTranslation = $travelTranslationRepository->findOneBy(
                [
                    'travel' => $travelTranslation->getTravel(),
                    'lang' => $otherLangArray,
                ]
            );
            $urlArray[$i]['travel'] = $otherTravelTranslation->getUrl();
            ++$i;
        }
        // End Locale switcher

        $travel = $this->travelRepository->showTravelFromTranslationSlug($travel);

        $travelObject = $this->travelRepository->find($travel['id']);

        // BREADCRUMBS //
        // Get Content
        // 1. Get the lang object for $locale
        $lang = $this->langRepository->findOneBy(['iso_code' => $locale]);
        // 2. Get the CategoryTranslation for that route
        $categoryTranslation2 = $categoryTranslationRepository->findOneBy([
            'title' => $categoryTranslation->getTitle(),
            'lang' => $lang,
        ]);

        $this->breadcrumbsHelper->destinationsByTravelDestinationBreadcrumbs($locale, $categoryTranslation, $categoryTranslation2, $travelTranslation);
        // END BREADCRUMBS //

        $dates = $this->datesRepository->showNextDates($travel);

        return $this->render('frontend/destination_single.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'travel' => $travel,
            'travelObject' => $travelObject,
            'dates' => $dates,
            'category' => $category,
        ]);
    }

    #[Route(path: ['en' => '{_locale}/trips/{category}/', 'es' => '{_locale}/destinos/{category}/', 'fr' => '{_locale}/destinations/{category}/'], name: 'destinations-by-category')]
    public function DestinationsByCategory(
        Request $request,
        CategoryTranslationRepository $categoryTranslationRepository,
        string $_locale = null,
        $locale = 'es'
    ): Response {
        $locale = $_locale ? $_locale : $locale;

        $categorySlug = $request->attributes->get('category');
        $categoryTranslation = $categoryTranslationRepository->findOneBy(['slug' => $categorySlug]);

        $category = $categoryTranslation->getCategory();

        $otherLangsArray = $this->langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();

            $otherCategoryTranslation = $categoryTranslationRepository->findOneBy(
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
        $lang = $this->langRepository->findOneBy(['iso_code' => $locale]);
        // 2. Get the CategoryTranslation for that route
        $categoryTranslation2 = $categoryTranslationRepository->findOneBy([
            'title' => $categoryTranslation->getTitle(),
            'lang' => $lang,
        ]);

        $this->breadcrumbsHelper->destinationsByCategoryBreadcrumbs($locale, $categoryTranslation, $categoryTranslation2);

        // 3. Get the travels for that category
        $travels = $this->travelRepository
            ->showByCategory(
                $locale,
                $categoryTranslation->getCategory()->getId()
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
            'locale' => $locale,
            'langs' => $urlArray,
            'travels' => $items,
            'category' => $categoryTranslation,
        ]);
    }

    #[Route(path: ['en' => '{_locale}/trips/', 'es' => '{_locale}/destinos/', 'fr' => '{_locale}/destinations/'], name: 'destinations')]
    public function Destinations(
        Request $request,
        breadcrumbsHelper $breadcrumbsHelper,
        ContinentsRepository $continentsRepository,
        string $_locale = null,
        $locale = 'es'
    ): Response {
        $locale = $_locale ? $_locale : $locale;
        $route = $request->attributes->get('_route');
        $breadcrumbsHelper->destinationsBreadcrumbs();

        $otherLangsArray = $this->langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }

        // Get the category for that translation
        $continent = $continentsRepository->findOneBy(['code' => 'as']);
        $categories = $this->categoryRepository->findCategoriesForIndex($locale, $continent);
        $otherDestinations = $this->categoryRepository->findOtherCategoriesForIndex($locale, $continent);

        $slug = $this->menuTranslationRepository->findCorrespondingRoute($route, $locale);

        return $this->render('destinations/index.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'categories' => $categories,
            'route' => $slug,
            'otherDestinations' => $otherDestinations,
        ]);
    }

    #[Route(path: ['en' => '{_locale}/calendar/', 'es' => '{_locale}/calendario/', 'fr' => '{_locale}/calendrier/'], name: 'calendar')]
    public function Calendar(
        Request $request,
        Breadcrumbs $breadcrumbs,
        LangRepository $langRepository,
        $locale = 'es',
        string $_locale = null
    ): Response {
        $locale = $_locale ? $_locale : $locale;
        $this->breadcrumbsHelper->calendarBreadcrumbs();

        $slug = $request->attributes->get('slug');
        $otherLangsArray = $langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }

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
                $calendarDates[$j]['year']['months'][$k] = $month;
                $calendarDates[$j]['year']['months'][$k]['dates'] = $this->datesRepository->showDatesByMonth($month, $year);
                ++$k;
            }
            ++$j;
        }

        return $this->render('calendar/index.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'dates' => $calendarDates,
            'route' => $slug,
        ]);
    }

    #[Route(path: ['en' => '{_locale}/{slug}/', 'es' => '{_locale}/{slug}/', 'fr' => '{_locale}/{slug}/'], name: 'main-content')]
    public function main(
        string $slug,
        string $locale = 'es',
        string $_locale = null
    ): Response {
        $locale = $_locale ? $_locale : $locale;
        $pageTranslation = $this->pageTranslationRepository->findOneBy(['slug' => $slug]);
        $page = $pageTranslation->getPage();

        $this->breadcrumbsHelper->mainBreadcrumbs($locale, $page);

        $otherLangsArray = $this->langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            $otherPageTranslation = $this->pageTranslationRepository->findOneBy(
                [
                    'Page' => $pageTranslation->getPage(),
                    'lang' => $otherLangArray,
                ]
            );
            $urlArray[$i]['page'] = $otherPageTranslation->getSlug();
            ++$i;
        }

        // Get Content
        // 1. Get the lang object for $locale
        $lang = $this->langRepository->findOneBy(['iso_code' => $locale]);
        // 2. Get the menuTranslation for that route
        $menuTranslation = $this->menuTranslationRepository->findOneBy([
            'slug' => $slug,
            'lang' => $lang,
        ]);

        // 3. Get the menu for that menu
        $menu = $this->menuRepository->find($menuTranslation->getMenu()->getId());

        // 4. Get the page translation for that page and for that locale
        $pageTranslation = $this->pageTranslationRepository->findOneBy([
            'Page' => $menu->getPage()->getId(),
            'lang' => $lang,
        ]);

        return $this->render('presentation/index.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'contents' => $pageTranslation,
            'page' => $slug,
            'slug' => $pageTranslation->getSlug(),
        ]);
    }

    #[Route(path: '/ajax/show-popup', options: ['expose' => true], name: 'ajax-show-popup')]
    public function showPopup(PopupsRepository $popupsRepository)
    {
        $popup = $popupsRepository->showPopup('es');
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
