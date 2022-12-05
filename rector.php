<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\SymfonyLevelSetList;

/* return static function (RectorConfig $rectorConfig): void { */
    /* $rectorConfig->paths([
        __DIR__ . '/config',
        __DIR__ . '/migrations',
        __DIR__ . '/node_modules',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/trash',
    ]); */

    // register a single rule
    //$rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    //    $rectorConfig->sets([
    //        LevelSetList::UP_TO_PHP_80
    //    ]);

/*     $rectorConfig->symfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml'); */

   /*  $rectorConfig->sets([
        SymfonyLevelSetList::UP_TO_SYMFONY_54,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ]); */
/* }; */

return static function (ContainerConfigurator $containerConfigurator): void {
    // region Symfony Container
    $parameters = $containerConfigurator->parameters();
    $parameters->set(
        Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
        __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml'
    );
    // endregion
    $containerConfigurator->import(SymfonySetList::SYMFONY_52);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
};

