<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Lang;
use App\Entity\Menu;
use App\Entity\MenuLocation;
use App\Entity\MenuTranslation;
use App\Entity\Texts;
use Doctrine\ORM\EntityManagerInterface;

class contentHelper
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function renderText(array $parameters)
    {
        $langRepository = $this->em->getRepository(Lang::class);
        $lang = $langRepository->findOneBy([
            'iso_code' => $parameters['locale'],
        ]);
        $TextsRepository = $this->em->getRepository(Texts::class);
        $text = $TextsRepository->findOneBy([
            'section' => $parameters['section'],
            'acronym' => $parameters['acronym'],
            'lang' => $lang,
        ]);

        return $text->getText();
    }

    public function renderMenu($locale, $location)
    {
        $menuRepository = $this->em->getRepository(Menu::class);
        /* $lang = $this->getLang($locale);
        $menuLocationRepository = $this->em->getRepository(MenuLocation::class);
        $menuLocationObject = $menuLocationRepository->findBy([
            'name' => $location
        ]);


        $menuItems = $menuRepository->findBy([
            'visibility' => true,
            'menuLocations' => $menuLocationObject[0]
            ],[
                'position' => 'ASC'
            ]);
        $menuTranslationsRepository = $this->em->getRepository(MenuTranslation::class);
        $menuElements = [];
        $i = 0;
        foreach ($menuItems as $menuItem) {
            $menuElements[$i] = $menuTranslationsRepository->findOneBy([
                'lang' => $lang,
                'Menu' => $menuItem
            ]);
            $i++;
        }
        $menuRepository->findMenyByLocation($locale, $location); */
        // dd($menuElements);
        /* $menuTranslation = $menuTranslationsRepository->findBy([
            'lang' => $lang
        ]); */
        return $menuRepository->findMenyByLocation($locale, $location);
    }

    public function getLang($locale)
    {
        $langs = $this->em->getRepository(Lang::class);
        $lang = $langs->findBy(['iso_code' => $locale]);

        return $lang;
    }

    public function renderCategoryList($locale)
    {
        $categoryRepository = $this->em->getRepository(Category::class);
        $categories = $categoryRepository->footerList($locale['locale']);

        return $categories;
    }
}
