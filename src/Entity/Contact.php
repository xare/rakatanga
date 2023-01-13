<?php

namespace App\Entity;

use Adamski\Symfony\PhoneNumberBundle\Validator as AssertPhoneNumber;
use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 20)]
    #[ORM\Column()]
    private ?string $firstname = null;

    /**
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 40)]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $lastname = null;

    #[AssertPhoneNumber\PhoneNumber]
    #[ORM\Column(nullable: true)]
    private ?string $phone = null;

   
    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column()]
    private ?string $email = null;

    /**
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 1000)]
    #[ORM\Column(type: 'text')]
    private ?string $message = null;

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
