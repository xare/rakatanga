<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SetFromListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
          MessageEvent::class => 'onMessage',
        ];
    }

    public function onMessage(MessageEvent $event)
    {
        $email = $event->getMessage();
        if (!$email instanceof Email) {
            return;
        }
        $email->from(new Address('webmaster@rakatanga-tour.com', 'Rakatanga Tour'));
    }
}
