<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Adamski\Symfony\PhoneNumberBundle\Model\PhoneNumber;
use Adamski\Symfony\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=ContactRepository::class)
 */
class Contact
{
    use TimestampableEntity;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 20)]
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 40)]
    private $lastname;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     * @var string|null
     * @AssertPhoneNumber
     */
    #[Assert\NotBlank]
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string|null 
     */
    #[Assert\NotBlank]
    #[Assert\Email]
    private $email;

    /**
     * @ORM\Column(type="text")
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 1000)]
    private $message;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

}
