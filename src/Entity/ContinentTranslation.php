<?php

namespace App\Entity;

use App\Repository\ContinentTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContinentTranslationRepository::class)]
class ContinentTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column()]
    private ?string $Name = null;

    #[ORM\ManyToOne(targetEntity: Continents::class, inversedBy: 'continentTranslation', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private Continents $continents;

    #[ORM\ManyToOne(targetEntity: Lang::class, inversedBy: 'continentTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private Lang $lang;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(?string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getContinents(): ?Continents
    {
        return $this->continents;
    }

    public function setContinents(?Continents $continents): self
    {
        $this->continents = $continents;

        return $this;
    }

    public function getLang(): ?Lang
    {
        return $this->lang;
    }

    public function setLang(?Lang $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    public function __toString()
    {
        return $this->Name;
    }
}
