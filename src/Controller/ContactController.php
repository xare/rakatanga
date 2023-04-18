<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Manager\ContactManager;
use App\Repository\LangRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class ContactController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator)
    {
    }

    #[Route(
        path: [
            'en' => '{_locale}/contact/',
            'es' => '{_locale}/contacto/',
            'fr' => '{_locale}/contact/'],
        name: 'contact')]
    public function index(
        Request $request,
        Breadcrumbs $breadcrumbs,
        LangRepository $langRepository,
        ContactManager $contactManager,
        string $_locale = null,
        string $locale = 'es'
    ): Response {
        $locale = $_locale ?: $locale;

        // LANG MENU
        $otherLangsArray = $langRepository->findOthers($locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }
        // END LANG MENU

        // BREADCRUMB
        $breadcrumbs->addItem('  '.$this->translator->trans('Contacto').' ');
        $breadcrumbs->prependRouteItem('Inicio', 'index');
        // END BREADCRUMB

        $lang = $langRepository->findOneBy(['iso_code' => $locale]);

        $form = $this->createForm(ContactType::class, null, ['lang' => $lang]);
        $form->handleRequest($request);
        $contactModel = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            // SAVE CONTACT
            $contactModel = $form->getData();
            $contact = new Contact();
            $contact->setFirstname($contactModel->getFirstname());
            $contact->setLastname($contactModel->getLastname());
            $contact->setEmail($contactModel->getEmail());
            $contact->setPhone($contactModel->getPhone());
            $contact->setMessage($contactModel->getMessage());
            $contact->setCreatedAt(new \DateTime());
            $contact->setUpdatedAt(new \DateTime());
            // SEND EMAILS
            $travels = $form->get('travel')->getData();
            $contactManager->sendToContact($contact, $travels);

            return $this->render('contact/success.html.twig', [
                'locale' => $locale,
                'langs' => $urlArray,
                'success' => $this->translator->trans('Has enviado el mensaje.'),
            ]);
        }

        return $this->render('contact/index.html.twig', [
            'locale' => $locale,
            'langs' => $urlArray,
            'form' => $form->createView(),
        ]);
    }
}
