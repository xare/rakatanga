<?php

namespace App\Service;

use App\Entity\Mailings;
use App\Entity\Reservation;
use App\Entity\User;
use ContainerAWpjPrA\getLoaderInterfaceService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bridge\Twig\Mime\WrappedTemplatedEmail;
use Symfony\Bundle\TwigBundle\DependencyInjection\Compiler\TwigEnvironmentPass;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class Mailer
{
    const MAIL_TITLE = "Rakatanga-tour";
    
    /**
     *
     * @var MailerInterface
     */
    private $mailer;

    /**
     *
     * @var pdfHelper
     */
    private $pdfHelper;

    /**
     *
     * @var KernelInterface
     */
    private $appKernel;

    /**
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     *
     * @var localizationHelper
     */
    private $localizationHelper;

    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;
    
    /**
     * @var ContainerInterface 
     */
    private $container;

    /**
     *
     * @var Environment
     */
    private $twig;

    /**
     * MailerService constructor
     * 
     * @param MailerInterface $mailer
     * @param pdfHelper $pdfHelper
     * @param KernelInterface $appKernel
     * @param localizationHelper $localizationHelper
     * @param EntityManagerInterface $entityManager
     * @param ContainerInterface $container
     * @param Environment $twig
     * 
     */

    public function __construct(
        MailerInterface $mailer,
        pdfHelper $pdfHelper,
        KernelInterface $appKernel,
        TranslatorInterface $translator,
        localizationHelper $localizationHelper,
        EntityManagerInterface $entityManager,
        ContainerInterface $container,
        Environment $twig
    ) {
        $this->mailer = $mailer;
        $this->pdfHelper = $pdfHelper;
        $this->appKernel = $appKernel;
        $this->translator = $translator;
        $this->localizationHelper = $localizationHelper;
        $this->entityManager = $entityManager;
        $this->container = $container;
        $this->twig = $twig;
    }

    public function send(
        string $subject, 
        string $from, 
        string $to, 
        string $template, 
        array $parameters): void
    {
        try {
            $email = (new TemplatedEmail())
                ->from($from)
                ->to($to)
                ->subject($subject)
                ->html(
                    $this->twig->render($template, $parameters)
                );

            $this->mailer->send($email);
        } catch (TransportException $e) {
            print $e->getMessage()."\n";
            throw $e;
        }

    }
    private function sendToSender()
    {
        return $email = (new TemplatedEmail());
        //->from(new Address('webmaster@rakatanga-tour.com', "Rakatanga Tours"));
    }
    private function sendToUs($email, $name, $surname)
    {
        return $email = (new TemplatedEmail())
            ->to(new Address('webmaster@rakatanga-tour.com', "Rakatanga Tours"));
    }
    private function sendToUser($user) {
        return $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getPrenom() . " ". $user->getNom()));
    }

    public function sendToContactSender($contact)
    {

        $subject = $this->translator->trans('Tu mensaje a Rakatanga Tours',[],'email');
        $email = $this->sendToSender();
        $email->to(new Address($contact->getEmail(), $contact->getFirstname()))
            ->subject($subject)
            ->htmlTemplate('email/contact/contact-sender.html.twig')
            ->context([
                /* 'contact' => $contact */]);
        
        $mailings = new Mailings();
        $mailings->setSubject($subject);
        $mailings->setToAddresses($contact->getEmail());
        $template = $this
                        ->twig
                        ->render('email/contact/render/contact-sender.html.twig');
        $mailings->setContent($template);
        $this->entityManager->persist($mailings);
        $this->entityManager->flush();

        $this->mailer->send($email);
    }
    public function sendToContactUs($contact, $travels)
    {
        $subject = "[".\App\Service\Mailer::MAIL_TITLE." - CONTACTO] Mensaje enviado por {$contact->getFirstname()} {$contact->getLastname()}";
        $email = $this->sendToUs(
            $contact->getEmail(),
            $contact->getFirstname(),
            $contact->getLastname()
        );
        $email->to(new Address('webmaster@rakatanga-tour.com', "Rakatanga Tours"))
            ->subject($subject)
            ->htmlTemplate('email/contact/contact-rakatanga.html.twig')
            ->context([
                'contact' => $contact,
                'travels' => $travels
            ]);
        
        $template = $this
            ->twig
            ->render('email/contact/render/contact-rakatanga.html.twig',[
                'contact' => $contact,
                'travels' => $travels
            ]);

        $mailings = new Mailings();
        $mailings->setSubject($subject);
        $mailings->setToAddresses($contact->getEmail());
        $mailings->setContent($template);
        $this->entityManager->persist($mailings);
        $this->entityManager->flush();
        $this->mailer->send($email);
    }

    public function sendRegistrationToUser($user, $verificationUrl)
    {
        $email = $this->sendToSender();
        $email->to(new Address($user->getEmail(), $user->getPrenom() . " " . $user->getNom()))
            ->subject($this->translator->trans('Has creado una cuenta en rakatanga-tour.com',[],'email'))
            ->htmlTemplate('email/registration/new-user-sender.html.twig')
            ->context([
                'user' => $user,
                'verificationUrl' => $verificationUrl
            ]);
        $this->mailer->send($email);
    }
    public function sendRegistrationToUs(User $user)
    {
        $email = $this->sendToUs($user->getEmail(), $user->getPrenom(), $user->getNom());
        $email
            ->subject("[".\App\Service\Mailer::MAIL_TITLE." - NUEVO USUARIO]  {$user->getPrenom()} {$user->getNom()}")
            ->htmlTemplate('email/registration/new-user-to-us.html.twig')
            ->context([
                'user' => $user
            ]);
        $this->mailer->send($email);
    }

    public function sendRegistrationVerificationToUser($user, $verificationUrl)
    {
        $email = $this->sendToSender();
        $email->to(
            new Address(
                $user->getEmail(),
                $user->getPrenom() . " " . $user->getNom()
            )
        )
            ->subject("[".\App\Service\Mailer::MAIL_TITLE."] {$this->translator->trans('Tu vínculo de verificación', [],'email')}")
            ->htmlTemplate('email/registration/new-user-verification-sender.html.twig')
            ->context([
                'user' => $user,
                'verificationUrl' => $verificationUrl
            ]);
        $this->mailer->send($email);
    }

    public function sendReservationPaymentSuccessToUs(Reservation $reservation, $locale)
    {
        $user = $reservation->getUser();
        $email = $this->sendToUs(
            $user->getEmail(),
            $user->getPrenom(),
            $user->getNom()
        );

        $email
            ->subject("[". \App\Service\Mailer::MAIL_TITLE." - {$this->translator->trans('PAGO')} ] {$this->translator->trans('Pago Realizado', [], 'email')} {$user->getPrenom()} {$user->getNom()}")
            ->htmlTemplate('email/reservation/reservation-payment-success-rakatanga.html.twig')
            ->context([
                'locale' => $locale,
                'reservation' => $reservation
            ]);
        $this->mailer->send($email);
    }

    public function sendReservationPaymentSuccessToSender(
        $reservation
    ) {
        $email = $this->sendToSender();
        $user = $reservation->getUser();
        $email->to(new Address($user->getEmail(), $user->getPrenom() . " " . $user->getNom()))
            ->subject("[".\App\Service\Mailer::MAIL_TITLE." - ".$this->translator->trans('PAGO')."] ". $this->translator->trans('Confirmación de pago para el viaje')." {$reservation->getDate()->getTravel()->getMainTitle()} ".$this->translator->trans('del')." {$reservation->getDate()->getDebut()->format('d/m/Y')}")
            ->htmlTemplate('email/reservation/reservation-payment-success-sender.html.twig')
            ->context([
                'reservation' => $reservation
            ]);
        $this->mailer->send($email);
    }

    public function sendReservationToUs(Reservation $reservation, $locale)
    {
        $user = $reservation->getUser();
        $email = $this->sendToUs(
            $user->getEmail(),
            $user->getPrenom(),
            $user->getNom()
        );

        $email
            ->subject("[".\App\Service\Mailer::MAIL_TITLE." - Reserva Guardada]  {$user->getPrenom()} {$user->getNom()}")
            ->htmlTemplate('email/reservation/reservation-saved-rakatanga.html.twig')
            ->context([
                'locale' => $locale,
                'reservation' => $reservation,
                'reference' => $reservation->getCode()
            ]); 
        $this->mailer->send($email);
    }

    public function sendReservationToSender(
        $reservation
    ) {
        $email = $this->sendToSender();
        $user = $reservation->getUser();
        $subject = "[".\App\Service\Mailer::MAIL_TITLE." - {$this->translator->trans("RESERVA REALIZADA",[],'email')}]  {$this->translator->trans("Has realizado una reserva para el viaje",[],'email')} {$reservation->getDate()->getTravel()->getMainTitle()} {$this->translator->trans("del ")} {$reservation->getDate()->getDebut()->format('d/m/Y')}";
        
        $to = new Address($user->getEmail(), $user->getPrenom() . " " . $user->getNom());
        $email->to($to)
            ->subject($subject)
            ->htmlTemplate('email/reservation/reservation-saved-sender.html.twig')
            ->context([
                'reservation' => $reservation,
                'reference' => $reservation->getCode()
            ]);
        $path = $this->appKernel->getProjectDir() . $this->pdfHelper::INVOICES_FOLDER;
       
        dump($path);
            foreach($reservation->getInvoices() as $invoice) {
                dump($invoice);
                $file = fopen($path . $invoice->getFilename(), 'r');
                $email->attach($file, $invoice->getFilename());
            }
            
            
            
        $mailing = new Mailings();
        $mailing->setSubject($subject);
        $mailing->setToAddresses($to->getAddress());
        
        $template = $this
                        ->twig
                        ->render('email/reservation/render/reservation-saved-sender.html.twig',[
                            'reservation' => $reservation,
                            'email' => null,
                            'reference' => strtoupper(substr($reservation->getDate()->getTravel()->getMainTitle(),0,3))."-".$reservation->getId()
                        ]);
        $mailing->setContent($template);
        $mailing->setReservation($reservation);
        $this->entityManager->persist($mailing);
        $this->entityManager->flush();

        $this->mailer->send($email);
    }


    public function sendInvoiceToCustomer(
        $invoicePdf,
        $reservation,
        $invoiceNumber
    ) {
        $email = $this->sendToSender();
        $user = $reservation->getUser();
        $path = $this->appKernel->getProjectDir() . $this->pdfHelper::INVOICES_FOLDER;

        $file = fopen($path . $invoicePdf, 'r');

        $email->to(new Address(
            $user->getEmail(),
            $user->getPrenom() . " " . $user->getNom()
        ))
            ->subject("[".\App\Service\Mailer::MAIL_TITLE." - RESERVA - FACTURA] Tu factura para el viaje {$reservation->getDate()->getTravel()->getMainTitle()} del { $reservation->getDate()->getDebut()->format('d/m/Y')}")
            ->htmlTemplate('email/send_pdf.html.twig')
            ->context([
                'reservation' => $reservation
            ])
            ->attach($file, "FACTURA-" . $invoiceNumber . ".pdf");
        $this->mailer->send($email);
    }

    public function sendEmailonDataCompletionToUs($reservation)
    {
        $user = $reservation->getUser();
        $email = $this->sendToUs(
            $user->getEmail(),
            $user->getPrenom(),
            $user->getNom()
        );
        $email
            ->subject("[".\App\Service\Mailer::MAIL_TITLE." - RESERVA - DATOS] Mensaje enviado por {$user->getPrenom()} {$user->getNom()}")
            ->htmlTemplate('email/reservation/reservation-data-rakatanga.html.twig')
            ->context([
                'reservation' => $reservation
            ]);
        $this->mailer->send($email);
    }

    public function sendCheckinMessage($reservation){
        $user = $reservation->getUser();
        $email = $this->sendToUser($user);
        $email
            ->subject("[".\App\Service\Mailer::MAIL_TITLE." - RESERVA - CHECK-IN] Introduce tus datos antes de tu viaje  {$user->getPrenom()} {$user->getNom()}")
            ->htmlTemplate('email/reservation/reservation-checkin-message.html.twig')
            ->context([
                'reservation' => $reservation
            ]);
        $this->mailer->send($email);

    }

}