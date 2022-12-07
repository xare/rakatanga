<?php

namespace App\Entity;

use App\Repository\PopupsTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PopupsTranslationRepository::class)]
class PopupsTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column()]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: Lang::class, inversedBy: 'popupsTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private Lang $lang;

    #[ORM\ManyToOne(targetEntity: Popups::class, inversedBy: 'popupsTranslations', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Popups $popup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

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

    public function getPopup(): ?Popups
    {
        return $this->popup;
    }

    public function setPopup(?Popups $popup): self
    {
        $this->popup = $popup;

        return $this;
    }
}
