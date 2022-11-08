<?php

namespace App\EventSubscriber;

use App\Event\ContactEvent;
use App\Service\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactSubscriber implements EventSubscriberInterface
{

  /**
   * @var Mailer $mailer
   */
  private $mailer;

  public function __construct(Mailer $mailer ) 
  {
    $this->mailer = $mailer;
  }
  
  /**
   *
   * @param ContactEvent $contactEvent
   * @return void
   */
  public function onSendContact(ContactEvent $contactEvent)
  {
    $contact = $contactEvent->getContact();
    $travels = $contactEvent->getTravels();

    $this->mailer->sendToContactSender($contact);
    $this->mailer->sendToContactUs($contact, $travels);
  }

  /**
   *
   * @return Array
   */
  public static function getSubscribedEvents(): Array
  {
    return [
      ContactEvent::class =>[
        ['onSendContact', 1]
      ]
    ];
  }
}
