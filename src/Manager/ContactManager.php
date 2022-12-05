<?php

namespace App\Manager;

use App\Entity\Contact;
use App\Event\ContactEvent;
use App\Service\logHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContactManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(
    EntityManagerInterface $entityManager,
    EventDispatcherInterface $eventDispatcher,
    logHelper $logHelper
  ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->logHelper = $logHelper;
    }

     /**
      * @param Contact $contact $name
      */
     public function sendToContact(Contact $contact, ArrayCollection $travels): void
     {
         if ($contact instanceof Contact) {
             $this->entityManager->persist($contact);
             $this->entityManager->flush();
             $this->logHelper->logThis(
                 'Contact',
                 $contact->getFirstname().' '.$contact->getLastname().' ['.$contact->getEmail().'] ha enviado un mensaje desde el formulario de contacto',
                 [],
                 'contact'
             );

             $event = new ContactEvent($contact, $travels);
             $this->eventDispatcher->dispatch($event);
         }
     }
}
