<?php

namespace App\EventSubscriber;

use App\Event\ContactEvent;
use App\Service\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactSubscriber implements EventSubscriberInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @return void
     */
    public function onSendContact(ContactEvent $contactEvent)
    {
        $contact = $contactEvent->getContact();
        $travels = $contactEvent->getTravels();

        $this->mailer->sendToContactSender($contact);
        $this->mailer->sendToContactUs($contact, $travels);
    }

    public static function getSubscribedEvents(): array
    {
        return [
          ContactEvent::class => [
            ['onSendContact', 1],
          ],
        ];
    }
}
