<?php

namespace App\EventSubscriber;

use App\Repository\MenuRepository;
use App\Repository\MenuTranslationRepository;
use App\Service\contentHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $contentHelper;
    private $defaultLocale;

    public function __construct ( 
                            Environment $twig, 
                            contentHelper $contentHelper,
                            string $defaultLocale = 'es'
                             ) 
    {
        $this->twig = $twig;
        $this->contentHelper = $contentHelper;
        $this->defaultLocale = $defaultLocale;
    }
    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();

        if( $locale = $request->attributes->get('_locale') )
        {
            $request->getSession()->set('_locale', $locale);
        } else {
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }

        $this->twig->addGlobal('menu', $this->contentHelper->renderMenu($request->getLocale(), 'Header'));
        $this->twig->addGlobal('menuFooter', $this->contentHelper->renderMenu($request->getLocale(), 'Footer'));

    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}
