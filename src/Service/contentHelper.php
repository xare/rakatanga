<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Lang;
use App\Entity\Menu;
use App\Entity\MenuLocation;
use App\Entity\MenuTranslation;
use App\Entity\Texts;
use App\Repository\CategoryRepository;
use App\Repository\LangRepository;
use App\Repository\MenuRepository;
use App\Repository\TextsRepository;
use Doctrine\ORM\EntityManagerInterface;

class contentHelper
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private MenuRepository $menuRepository,
        private LangRepository $langRepository,
        private TextsRepository $textsRepository,
        private CategoryRepository $categoryRepository)
    {
    }

    public function renderText(array $parameters)
    {
        $lang = $this->langRepository->findOneBy([
            'iso_code' => $parameters['locale'],
        ]);
        $text = $this->textsRepository->findOneBy([
            'section' => $parameters['section'],
            'acronym' => $parameters['acronym'],
            'lang' => $lang,
        ]);

        return $text->getText();
    }

    public function renderMenu($locale, $location)
    {
        return $this->menuRepository->findMenuByLocation($locale, $location);
    }

    public function getLang($locale)
    {
        return $this->langRepository->findBy(['iso_code' => $locale]);
    }

    public function renderCategoryList($locale)
    {
        return $this->categoryRepository->footerList($locale['locale']);
    }
}
