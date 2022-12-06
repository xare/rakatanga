<?php

namespace App\Entity;

use Adamski\Symfony\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
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
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     *
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 20)]
    #[ORM\Column(type: 'string', length: 255)]
    private $firstname;

    /**
     *
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 40)]
    #[ORM\Column(type: 'string', length: 255)]
    private $lastname;

    /**
     *
     * @var string|null
     *
     * @AssertPhoneNumber
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    private $phone;

    /**
     *
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    /**
     *
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 1000)]
    #[ORM\Column(type: 'text')]
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
