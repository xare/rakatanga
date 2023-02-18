<?php

namespace App\Twig;

use App\Entity\Document;
use App\Entity\Reservation;
use App\Entity\ReservationData;
use App\Entity\Travellers;
use App\Entity\User;
use App\Service\contentHelper;
use App\Service\documentHelper;
use App\Service\localizationHelper;
use App\Service\reservationDataHelper;
use App\Service\reservationHelper;
use App\Service\slugifyHelper;
use App\Service\UploadHelper;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(private ContainerInterface $container, private string $publicDir)
    {
        $this->container = $container;
        $this->publicDir = $publicDir;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('slugify', [$this, 'slugifyThis']),
            new TwigFilter('cast_to_array', [$this, 'castToArray']),
            new TwigFilter('negativify', [$this, 'negativify']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('upload_asset', [$this, 'getUploadedAssetPath']),
            new TwigFunction('show_text', [$this, 'showText']),
            // new TwigFunction('show_menu', [$this, 'showMenu']),
            new TwigFunction('renderLocalized', [$this, 'getLocalizedTranslation']),
            new TwigFunction('renderLocalizedTravel', [$this, 'getLocalizedTravelTranslation']),
            new TwigFunction('renderLocalizedTravelUrl', [$this, 'getLocalizedTravelTranslationUrl']),
            new TwigFunction('renderLocalizedOption', [$this, 'getLocalizedOptionTranslation']),
            new TwigFunction('renderLocalizedOptionIntro', [$this, 'getLocalizedOptionTranslationIntro']),
            new TwigFunction('renderLocalizedCategory', [$this, 'getLocalizedCategoryTranslation']),
            new TwigFunction('renderLocalizedOptionInfodoc', [$this, 'getLocalizedOptionTranslationInfodoc']),
            new TwigFunction('renderLocalizedPopupTitle', [$this, 'getLocalizedPopupTitleTranslation']),
            new TwigFunction('renderLocalizedPopupContent', [$this, 'getLocalizedPopupContentTranslation']),
            new TwigFunction('render_category_list', [$this, 'renderCategoryList']),
            new TwigFunction('encore_entry_css_source', [$this, 'getEncoreEntryCssSource']),
            new TwigFunction('class', [$this, 'getClass']),
            new TwigFunction('renderReservationAmmount', [$this, 'getReservationAmmount']),
            new TwigFunction('renderReservationDuePayment', [$this, 'getReservationDuePayment']),
            new TwigFunction('listDocumentsByReservationByUser', [$this, 'getDocumentsByReservationByUser']),
            new TwigFunction('listDocumentsByReservationByTraveller', [$this, 'getDocumentsByReservationByTraveller']),
            new TwigFunction('hasDoctype', [$this, 'hasDoctype']),
            new TwigFunction('notPresentDoctypeUser', [$this, 'notPresentDoctypeUser']),
            new TwigFunction('notPresentDoctypeTraveller', [$this, 'notPresentDoctypeTraveller']),
            new TwigFunction('getReservationDataFields', [$this, 'getReservationDataFields']),
            new TwigFunction('getUserReservationDataFields',[$this, 'getUserReservationDataFields']),
            new TwigFunction('getTravellerReservationDataFields',[$this, 'getTravellerReservationDataFields'])
        ];
    }

    public function castToArray($stdClassObject): array
    {
        $response = [];
        foreach ($stdClassObject as $key => $value) {
            $response[] = [$key, $value];
        }

        return $response;
        /* return ["Hello!"]; */
    }

    public function getUploadedAssetPath(string $path): string
    {
        return $this->container
            ->get(UploadHelper::class)
            ->getPublicPath($path);
    }

    public function showText($parameters): string
    {
        return $this->container
            ->get(contentHelper::class)
            ->renderText($parameters);
    }

    public function showMenu($locale): array
    {
        return $this->container
            ->get(contentHelper::class)
            ->renderMenu($locale);
    }

    public function getLocalizedTranslation($entityName, $locale)
    {
        return $this->container
            ->get(localizationHelper::class)
            ->renderString($entityName, $locale);
    }

    public function getLocalizedTravelTranslation($id, $locale)
    {
        return $this->container
            ->get(localizationHelper::class)
            ->renderTravelString($id, $locale);
    }

    public function getLocalizedTravelTranslationUrl($id, $locale)
    {
        return $this->container
            ->get(localizationHelper::class)
            ->renderTravelUrl($id, $locale);
    }

    public function getLocalizedOptionTranslation($id, $locale)
    {
        return $this->container
            ->get(localizationHelper::class)
            ->renderOptionString($id, $locale);
    }

    public function getLocalizedOptionTranslationIntro($id, $locale)
    {
        return $this->container
            ->get(localizationHelper::class)
            ->renderOptionIntro($id, $locale);
    }

    public function getLocalizedOptionTranslationInfodoc($id, $locale)
    {
        return $this->container
            ->get(localizationHelper::class)
            ->renderOptionInfodoc($id, $locale);
    }

    public function getLocalizedCategoryTranslation($id, $locale)
    {
        return $this->container
            ->get(localizationHelper::class)
            ->renderCategoryString($id, $locale);
    }

    public function getLocalizedPopupTitleTranslation($id, $locale)
    {
        return $this->container
            ->get(localizationHelper::class)
            ->renderPopupTitle($id, $locale);
    }

    public function getLocalizedPopupContentTranslation($id, $locale)
    {
        return $this->container
            ->get(localizationHelper::class)
            ->renderPopupContent($id, $locale);
    }

    public function renderCategoryList($locale)
    {
        return $this->container
            ->get(contentHelper::class)
            ->renderCategoryList($locale);
    }

    public function slugifyThis($value)
    {
        return $this->container
            ->get(slugifyHelper::class)
            ->slugify($value);
    }

    public function negativify($value)
    {
        $newValue = -$value;

        return $newValue;
    }

    public function getEncoreEntryCssSource(string $entryName): string
    {
        $entryPointLookupInterface = $this->container->get(EntrypointLookupInterface::class);
        $entryPointLookupInterface->reset();
        $files = $entryPointLookupInterface->getCssFiles($entryName);
        $source = '';

        foreach ($files as $file) {
            $source .= file_get_contents($this->publicDir.'/'.$file);
        }

        return $source;
    }

    public function getClass($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }

    public function getReservationAmmount(Reservation $reservation)
    {
        return $this->container
        ->get(reservationDataHelper::class)
        ->getReservationAmmount($reservation);
    }

    public function getReservationDuePayment(Reservation $reservation)
    {
        return $this->container
            ->get(reservationDataHelper::class)
            ->getReservationDueAmmount($reservation);
    }

    public function getDocumentsByReservationByUser(Reservation $reservation)
    {
        return $this->container
            ->get(documentHelper::class)
            ->getDocumentsByReservationByUser($reservation);
    }

    public function getDocumentsByReservationByTraveller(Reservation $reservation, Travellers $traveller)
    {
        return $this->container
            ->get(documentHelper::class)
            ->getDocumentsByReservationByTraveller($reservation, $traveller);
    }

    public function hasDoctype(Document $document, string $doctype)
    {
        return $this->container
            ->get(documentHelper::class)
            ->hasDoctype($document, $doctype);
    }
    public function notPresentDoctypeUser(
        Reservation $reservation,
        User $user,
        string $doctype ) {
        return $this->container
            ->get(documentHelper::class)
            ->notPresentDoctypeUser($reservation, $user, $doctype);
    }

    public function notPresentDoctypeTraveller(
        Reservation $reservation,
        Travellers $traveller,
        string $doctype ) {
        return $this->container
            ->get(documentHelper::class)
            ->notPresentDoctypeTraveller($reservation, $traveller, $doctype);
    }

    public function getReservationDataFields(ReservationData $reservationData) {
        return $this->container
            ->get(reservationDataHelper::class)
            ->getReservationDataFields($reservationData);
    }
    public function getUserReservationDataFields(Reservation $reservation, User $user) {
        return $this->container
            ->get(reservationDataHelper::class)
            ->getUserReservationDataFields($reservation, $user);
    }

    public function getTravellerReservationDataFields(
        Reservation $reservation,
        Travellers $traveller ) {
        return $this->container
            ->get(reservationDataHelper::class)
            ->getTravellerReservationDataFields($reservation, $traveller);
    }

    public static function getSubscribedServices():array
    {
        return [
            documentHelper::class,
            UploadHelper::class,
            reservationHelper::class,
            reservationDataHelper::class,
            contentHelper::class,
            localizationHelper::class,
            slugifyHelper::class,
            EntrypointLookupInterface::class,
        ];
    }
}
