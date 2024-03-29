<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\LangRepository;
use App\Security\LoginFormAuthenticator;
use App\Service\breadcrumbsHelper;
use App\Service\logHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private breadcrumbsHelper $breadcrumbsHelper){}

    #[Route(
        path: '/login',
        options: ['expose' => true],
        name: 'app_login')]
    #[Route(
        path: [
            'en' => '{_locale}/login/', 
            'es' => '{_locale}/login/', 
            'fr' => '{_locale}/login/'],
        priority: 10,
        name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        LangRepository $langRepository,
        string $_locale = 'es'
        ): Response {

        $this->breadcrumbsHelper->loginBreadcrumbs();

        $otherLangsArray = $langRepository->findOthers($_locale);
        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }

        // get the login error if there is one

        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/user_login.html.twig', [
            'locale' => $_locale,
            'langs' => $urlArray,
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /*
     * @Route({
     *      "en": "{_locale}/register/",
     *      "es": "{_locale}/register/",
     *      "fr": "{_locale}/register/"
     *      }, name="app_register", priority=1)
     */

    /* public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        LoginFormAuthenticator $formAuthenticator,
        MailerInterface $mailer,
        logHelper $logHelper,
        $locale = 'es',
        $_locale = null
        )
    {
        $locale = $_locale ?: $locale;

       /*  $langArray = $this->langMenu($request, $locale);
        $navMenu = $this->showMenu($locale, $request);
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $userModel = $form->getData();
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            // be absolutely sure they agree
            if (true === $form['agreeTerms']->getData()) {
                $user->agreeTerms();
            }
            $user->setEmail($userModel->getEmail());
            $user->setPrenom($userModel->getPrenom());
            $user->setNom($userModel->getNom());
            $user->setTelephone($userModel->getTelephone());
            $user->setPosition($userModel->getPosition());

            $this->entityManager->persist($user);

            $logHelper->logNewUser($user);

            $this->entityManager->flush();
            // WE CLEAN THE ENTITY MANAGER FOR REUSE LATER
            // WE ADD A SUCCESS MESSAGE
            $this->addFlash('exito', $user::REGISTERED_SUCCESFULLY);
            $email = (new Email())
                ->from('xaresd@gmail.com')
                ->to('xare@katakrak.net')
                ->subject('Welcome to Rakatanga')
                ->text('A new user has been registered');
            $mailer->send($email);

        }
        return $this->render('security/register.html.twig',[
           /*  'menu'=>$navMenu,
            'locale'=>$locale,
            /* 'langs' => $langArray,
            'form' => $form->createView()
        ]);
    } */
}
