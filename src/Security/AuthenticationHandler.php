<?php

// AuthenticationHandler.php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;

class AuthenticationHandler extends AbstractAuthenticator
{
    public const LOGIN_ROUTE = 'app_login';

    /**
     * Constructor.
     *
     * @author 	Joe Sexton <joe@webtipblog.com>
     *
     * @param Session $session
     */
    public function __construct(private RouterInterface $router, private RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public function supports(Request $request): ?bool
    {
    }

    public function authenticate(Request $request): PassportInterface
    {
        // TODO: Implement authenticate() method.
    }

    /**
     * onAuthenticationSuccess.
     *
     * @author 	Joe Sexton <joe@webtipblog.com>
     *
     * @return Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // if AJAX login
        if ($request->isXmlHttpRequest()) {
            $array = ['success' => true]; // data to return via JSON
            $response = new Response(json_encode($array));
            $response->headers->set('Content-Type', 'application/json');

            return $response;

        // if form login
        } else {
            if ($this->requestStack->getSession()->get('_security.main.target_path')) {
                $url = $this->requestStack->getSession()->get('_security.main.target_path');
            } else {
                $url = $this->router->generate('index');
            } // end if

            return new RedirectResponse($url);
        }
    }

     /**
      * onAuthenticationFailure.
      *
      * @author 	Joe Sexton <joe@webtipblog.com>
      *
      * @return 	Response
      */
     public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
     {
         // if AJAX login
         if ($request->isXmlHttpRequest()) {
             $array = ['success' => false, 'message' => $exception->getMessage()]; // data to return via JSON
             $response = new Response(json_encode($array, JSON_THROW_ON_ERROR));
             $response->headers->set('Content-Type', 'application/json');

             return $response;

         // if form login
         } else {
             // set authentication exception to session
             $request->getSession()->set(SecurityContextInterface::AUTHENTICATION_ERROR, $exception);

             return new RedirectResponse($this->router->generate('login_route'));
         }
     }
}
