<?php

namespace App\Service;

use App\Entity\Dates;
use App\Entity\Reservation;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class breadcrumbsHelper
{

    public function __construct(
        private Breadcrumbs $breadcrumbs, 
        private TranslatorInterface $translator, 
        private localizationHelper $localizationHelper, 
        private slugifyHelper $slugifyHelper)
    {
    }

    public function destinationsBreadcrumbs()
    {
        $this->breadcrumbs->addItem($this->translator->trans('Nuestras rutas en moto'));
        $this->breadcrumbs->prependRouteItem($this->translator->trans('Inicio'), 'index');
    }

    public function destinationsByCategoryBreadcrumbs($locale, $categoryTranslation, $categoryTranslation2)
    {
        $this->breadcrumbs->addRouteItem($this->translator->trans('Nuestras rutas en moto'), 'destinations', [
          '_locale' => $locale,
        ]);
        $this->breadcrumbs->addRouteItem(ucfirst($categoryTranslation2->getTitle()), 'destinations-by-category', [
          '_locale' => $locale,
          'category' => $categoryTranslation->getTitle(),
        ]);
        $this->breadcrumbs->prependRouteItem('Inicio', 'index');
    }

    public function destinationsByTravelDestinationBreadcrumbs($locale, $categoryTranslation, $categoryTranslation2, $travelTranslation)
    {
        $this->breadcrumbs->addRouteItem(
            $this->translator->trans('Nuestras rutas en moto'),
            'destinations',
            [
                '_locale' => $locale,
            ]
        );
        $this->breadcrumbs->addRouteItem(
            $categoryTranslation->getTitle(),
            'destinations-by-category',
            [
                '_locale' => $locale,
                'category' => $categoryTranslation->getTitle(),
            ]
        );
        $this->breadcrumbs->addRouteItem(
            $travelTranslation->getTitle(),
            'destination',
            [
                '_locale' => $locale,
                'category' => $categoryTranslation->getTitle(),
                'travel' => $travelTranslation->getTitle(),
            ]
        );
        $this->breadcrumbs->prependRouteItem(
            $this
                ->translator
                ->trans('Inicio'),
            'index'
        );
    }

    public function calendarBreadcrumbs()
    {
        $this->breadcrumbs->addItem($this->translator->trans('Nuestras rutas en moto'));
        $this->breadcrumbs->prependRouteItem($this->translator->trans('Inicio'), 'index');
    }

    public function mainBreadcrumbs($locale, $page)
    {
        $this->breadcrumbs
                ->addItem(
                    $this->localizationHelper->renderPageString(
                        $page->getId(),
                        $locale
                    )
                );
        $this->breadcrumbs
            ->prependRouteItem($this->translator->trans('Inicio'), 'index');
    }

    public function blogBreacrumbs()
    {
        $this->breadcrumbs->addItem($this->translator->trans('Blog'));
        $this->breadcrumbs->prependRouteItem($this->translator->trans('Inicio'), 'index');
    }

    public function blogArticleBreadcrumbs($article, $slug)
    {
        $this->breadcrumbs->addRouteItem($this->translator->trans('Blogs'), 'blog');
        $this->breadcrumbs->addRouteItem($article->getTitle(), 'blog-article', ['slug' => $slug]);
        $this->breadcrumbs->prependRouteItem($this->translator->trans('Inicio'), 'index');
    }

    public function registerBreadcrumbs()
    {
        $this->breadcrumbs->addRouteItem($this->translator->trans('Crear cuenta'), 'register');
        $this->breadcrumbs->prependRouteItem($this->translator->trans('Inicio'), 'index');
    }

    public function loginBreadcrumbs()
    {
        $this->breadcrumbs->addRouteItem($this->translator->trans('Entrar'), 'app_login');
        $this->breadcrumbs->prependRouteItem($this->translator->trans('Inicio'), 'index');
    }

    public function reservationBreadcrumbs(string $locale, Dates $date): void
    {
        $this->breadcrumbs->addRouteItem($this->translator->trans('Nuestras rutas en moto'), 'destinations');
        $this->breadcrumbs->addRouteItem(
            $this->localizationHelper->renderCategoryString($date->getTravel()->getCategory()->getId(), $locale), 'destinations-by-category', [
                '_locale' => $locale,
                'category' => $this->slugifyHelper->slugify($this->localizationHelper->renderCategoryString($date->getTravel()->getCategory()->getId(), $locale)),
     ]);
        $this->breadcrumbs->addRouteItem(
            $this->localizationHelper->renderTravelString($date->getTravel()->getId(), $locale), 'destination', [
                '_locale' => $locale,
                'category' => $this->slugifyHelper->slugify($this->localizationHelper->renderCategoryString($date->getTravel()->getCategory()->getId(), $locale)),
                'travel' => $this->slugifyHelper->slugify($this->localizationHelper->renderTravelString($date->getTravel()->getId(), $locale)),
     ]);
        $this->breadcrumbs->addItem($this->translator->trans('Reserva'));
        $this->breadcrumbs->prependRouteItem($this->translator->trans('Inicio'), 'index');
    }

    public function reservationPaymentBreadcrumbs(string $locale, Reservation $reservation): void
    {
        $this->breadcrumbs->addRouteItem($this->translator->trans('Nuestras rutas en moto'), 'destinations');
        $this->breadcrumbs->addRouteItem(
            $this->localizationHelper->renderCategoryString($reservation->getDate()->getTravel()->getCategory()->getId(), $locale), 'destinations-by-category', [
                '_locale' => $locale,
                'category' => $this->slugifyHelper->slugify($this->localizationHelper->renderCategoryString($reservation->getDate()->getTravel()->getCategory()->getId(), $locale)),
         ]);
        $this->breadcrumbs->addRouteItem(
            $this->localizationHelper->renderTravelString($reservation->getDate()->getTravel()->getId(), $locale), 'destination', [
                '_locale' => $locale,
                'category' => $this->slugifyHelper->slugify($this->localizationHelper->renderCategoryString($reservation->getDate()->getTravel()->getCategory()->getId(), $locale)),
                'travel' => $this->slugifyHelper->slugify($this->localizationHelper->renderTravelString($reservation->getDate()->getTravel()->getId(), $locale)),
         ]);
        $this->breadcrumbs->addRouteItem($this->translator->trans('Reserva'), 'reservation', [
           '_locale' => $locale,
           'category' => $this->slugifyHelper->slugify($this->localizationHelper->renderCategoryString($reservation->getDate()->getTravel()->getCategory()->getId(), $locale)),
           'travel' => $this->slugifyHelper->slugify($this->localizationHelper->renderTravelString($reservation->getDate()->getTravel()->getId(), $locale)),
           'date' => $reservation->getDate()->getId(),
        ]);
        $this->breadcrumbs->addItem($this->translator->trans('Revisión y pago'));
        $this->breadcrumbs->prependRouteItem($this->translator->trans('Inicio'), 'index');
    }

    public function reservationTravellersBreadcrumbs(string $locale)
    {
        $this->breadcrumbs->addRouteItem(
            $this->translator->trans('Tus Reservas'),
            'frontend_user_reservations',
            [
                '_locale' => $locale,
            ]
        );
        $this->breadcrumbs->prependRouteItem(
            $this
                ->translator
                ->trans('Inicio'),
            'index'
        );
        $this->breadcrumbs->addItem(
            $this
                ->translator
                ->trans('Reserva')
        );
    }

    public function reservationDataBreadcrumbs(string $locale)
    {
    }

    public function userFrontendReservationsBreadcrumbs(string $locale){
        $this->breadcrumbs->addRouteItem(
            $this->translator->trans('Tus Reservas'),
            'frontend_user_reservations',
            [
                '_locale' => $locale,
            ]
        );
        $this->breadcrumbs->prependRouteItem(
            $this
                ->translator
                ->trans('Inicio'),
            'index'
        );
    }

    public function frontendUserSettingsBreadcrumbs(string $locale){
        $this->breadcrumbs->addRouteItem(
            $this->translator->trans('Usuario'),
            'frontend_user',
            [
                '_locale' => $locale,
            ]
        );
        $this->breadcrumbs->addRouteItem(
            $this->translator->trans('Datos del usuario'),
            'frontend_user_settings',
            [
                '_locale' => $locale,
            ]
        );
        $this->breadcrumbs->prependRouteItem(
            $this
                ->translator
                ->trans('Inicio'),
            'index'
        );
    }
}
