<?php

namespace App\Event;

use App\Entity\Contact;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Contracts\EventDispatcher\Event;

class ContactEvent extends Event
{
  const TEMPLATE_CONTACT_SENDER = "email/contact/contact-sender.html.twig";
  const TEMPLATE_CONTACT_RAKATANGA = "email/contact/contact-rakatanga.html.twig";

  /**
   * @param Contact $contact
   * @param array $travels
   */

   public function __construct(Contact $contact, ArrayCollection $travels)
   {
      $this->contact = $contact;
      $this->travels = $travels;
   }

   /**
    * @return Contact
    */
    public function getContact(): Contact
    {
      return $this->contact;
    }

    /**
     * @return array
     */
    public function getTravels(): ArrayCollection
    {
      return $this->travels;
    }
}