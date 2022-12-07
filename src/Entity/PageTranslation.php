<?php

namespace App\Entity;

use App\Repository\PageTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageTranslationRepository::class)]
class PageTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column()]
    private ?string $title = null;

    #[ORM\Column()]
    private ?string $slug = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $intro = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $body = null;

    #[ORM\ManyToOne(targetEntity: Lang::class, inversedBy: 'pageTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lang $lang = null;

    #[ORM\ManyToOne(targetEntity: Pages::class, inversedBy: 'pageTranslations', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Pages $Page;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setIntro(?string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

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

    public function getPage(): ?Pages
    {
        return $this->Page;
    }

    public function setPage(?Pages $Page): self
    {
        $this->Page = $Page;

        return $this;
    }

    /* public function __toString(){
        return $this->slug;
    } */
}
