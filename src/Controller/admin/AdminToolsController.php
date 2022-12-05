<?php

namespace App\Controller\admin;

use App\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/admin/tools')]
class AdminToolsController extends AbstractController
{
    private Mailer $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    #[Route(path: '/sendEmail', name: 'admin-send-email', methods: ['POST'], options: ['expose' => true])]
    public function adminSendEmail(Request $request): Response
    {
        $email = $request->request->get('email');
        $url = $request->request->get('url');
        $mailResult = $this->mailer->sendTravelUrlTo($email, $url);

        return $this->json([
          'email' => $email,
          'url' => $url,
          'mailResult' => $mailResult,
        ],
            200);
    }
}
