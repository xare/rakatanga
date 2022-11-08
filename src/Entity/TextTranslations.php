<?php

namespace App\Entity;

use App\Repository\TextTranslationsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TextTranslationsRepository::class)
 */
class TextTranslations
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Text;

    /**
     * @ORM\ManyToOne(targetEntity=Lang::class, inversedBy="textTranslations")
     */
    private $Lang;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): self
    {
        $this->Title = $Title;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->Text;
    }

    public function setText(string $Text): self
    {
        $this->Text = $Text;

        return $this;
    }

    public function getLang(): ?Lang
    {
        return $this->Lang;
    }

    public function setLang(?Lang $Lang): self
    {
        $this->Lang = $Lang;

        return $this;
    }
}
