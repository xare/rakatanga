<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\LangRepository;
use App\Repository\UserRepository;
use App\Service\breadcrumbsHelper;
use App\Service\logHelper;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegisterController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private breadcrumbsHelper $breadcrumbsHelper)
    {
        $this->entityManager = $entityManager;
        $this->breadcrumbsHelper = $breadcrumbsHelper;
    }

    #[Route(path: '/register', name: 'register', priority: 10)]
    #[Route(path: ['en' => '{_locale}/register', 'es' => '{_locale}/registro', 'fr' => '{_locale}/enregistrement'], name: 'register', priority: 10)]
    public function index(
    Request $request,
    UserPasswordHasherInterface $userPasswordHasher,
    Mailer $mailer,
    logHelper $logHelper,
    EntityManagerInterface $em,
    LangRepository $langRepository,
    VerifyEmailHelperInterface $verifyEmailHelper,
    string $_locale = null,
    string $locale = 'es'
  ): Response {
        $locale = $_locale ? $_locale : $locale;

        $this->breadcrumbsHelper->registerBreadcrumbs();

        // Swith Locale Loader
        $otherLangsArray = $langRepository->findOthers($locale);

        $i = 0;
        $urlArray = [];
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }
        $user = new User(); // I GET THE LOGGED IN USER
        if ($user->getEmail() !== null) { // WE NEED ANOTHER METHOD TO CHECK LOGGED IN STATUS.
      // IF USER IS LOGGED IN REDIRECT TO DASHBOARD.
            return $this->redirectToRoute('dashboard');
        } else {
            // ELSE SHOW REGISTRATION PAGE
            $form = $this->createForm(UserType::class, $user);
            // WE CHECK NOW IF THE form has been submitted.
            $form->handleRequest($request);
            // IS SUBMITTED AND VALID
            if ($form->isSubmitted() && $form->isValid()) {
                // WE ACCESS THE USER ENTITY AND WE SET A PASSWORD
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form['password']->getData()
                    )
                );

                $user->setRoles(['ROLE_USER']);
                // THIS COMMAND MAKES AN INSERT IN THE DATABASE AT TABLE USER
                $em->persist($user);
                // WE CLEAN THE ENTITY MANAGER FOR REUSE LATER
                $logHelper->logNewUser($user);

                // WE ADD A SUCCESS MESSAGE
                $this->addFlash('exito', $user::REGISTERED_SUCCESFULLY);

                // WE SEND EMAILS
                $signatureComponents = $verifyEmailHelper->generateSignature(
                    'user_verify_email',
                    $user->getId(),
                    $user->getEmail(),
                    ['id' => $user->getId()]
                );
                $verificationUrl = $signatureComponents->getSignedUrl();
                $mailer->sendRegistrationVerificationToUser($user, $verificationUrl, $locale);
                $mailer->sendRegistrationToUs($user);

                $formVerification = $this->createFormBuilder()
                    ->add('userId', HiddenType::class, [
                        'data' => $user->getId(),
                    ])
                    ->add('send', SubmitType::class)
                    ->getForm();
                $formVerification->handleRequest($request);

                if ($request->isXmlHttpRequest()) {
                    return $this->render('security/_form_verification.html.twig', [
                      'form' => $formVerification->createView(),
                      'userId' => $user->getId(),
                    ]);
                }

                return $this->render('security/verification.html.twig', [
                  'locale' => $locale,
                  'langs' => $urlArray,
                  'form' => $formVerification->createView(),
                  'userId' => $user->getId(),
                  'user' => $user,
                  'static' => 'static',
                ]);
            }
            if ($request->isXmlHttpRequest()) {
                $html = $this->renderView('security/_form_register_modal.html.twig', [
                  'formRegister' => $form->createView(),
                ]);
                $state = 200;

                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
                        dd('valid');
                    } else {
                        $state = 400;
                    }
                }

                return new Response($html, $state);
            }

            return $this->render('security/register.html.twig', [
              'locale' => $locale,
              'formRegister' => $form->createView(),
              'langs' => $urlArray,
              'static' => 'static',
            ]);
        }
    }

    #[Route(path: '/verify/{user}', name: 'user-verify')]
    public function userVerify(
                        User $user,
                        EntityManagerInterface $em,
                        Request $request,
                        LangRepository $langRepository,
                        UserAuthenticatorInterface $userAuthenticator,
                        FormLoginAuthenticator $formLoginAuthenticator,
                        RememberMeHandlerInterface $rememberMe,
                        $_locale = null,
                        string $locale = 'es'
  ) {
        $locale = $_locale ? $_locale : $locale;

        // Swith Locale Loader
        $otherLangsArray = $langRepository->findOthers($locale);
        $urlArray = [];
        $i = 0;
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }
        // End switch locale

        // Create verification form
        $form = $this->createFormBuilder()
          ->add('verification', TextType::class)
          ->add('userId', HiddenType::class, [
            'data' => $user->getId(),
          ])
          ->add('send', SubmitType::class)
          ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['verification'] == $user->getVerification()) {
                $user->setVerification('verified');
                $em->persist($user);
                $em->flush();
                $userAuthenticator->authenticateUser(
                    $user,
                    $formLoginAuthenticator,
                    $request
                );
                $rememberMe->createRememberMeCookie($this->getUser());
                // dd($user);
                return $this->redirectToRoute('frontend_user');
            }
        }

        return $this->render('security/verification.html.twig', [
          'locale' => $locale,
          'langs' => $urlArray,
          'form' => $form->createView(),
          'userId' => $user->getId(),
          'user' => $user,
          'static' => 'static',
        ]);
    }

    #[Route(path: '/verify', name: 'user_verify_email')]
    public function userVerifyEmail(
    Request $request,
    VerifyEmailHelperInterface $verifyEmailHelper,
    LangRepository $langRepository,
    UserRepository $userRepository,
    $_locale = null,
    string $locale = 'es'
  ) {
        $user = $userRepository->find($request->query->get('id'));
        if (!$user) {
            throw $this->createNotFoundException();
        }
        // Swith Locale Loader
        $otherLangsArray = $langRepository->findOthers($locale);
        $urlArray = [];
        $i = 0;
        foreach ($otherLangsArray as $otherLangArray) {
            $urlArray[$i]['iso_code'] = $otherLangArray->getIsoCode();
            $urlArray[$i]['lang_name'] = $otherLangArray->getName();
            ++$i;
        }
        // End switch locale

        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
            $user->setIsVerified(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->render('security/verify.html.twig', [
              'langs' => $urlArray,
            ]);

            return new Response('Email got validated');
        } catch (VerifyEmailExceptionInterface $e) {
            dd($e->getReason());

            return new Response($e->getReason(), \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED);
        }
    }
}
