<?php

namespace App\Entity;

use App\Repository\OptionsTranslationsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OptionsTranslationsRepository::class)]
class OptionsTranslations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column()]
    private ?string $title = null;

    #[ORM\Column(type:'text', nullable: true)]
    private ?string $intro = null;

    #[ORM\ManyToOne(targetEntity: Lang::class, inversedBy: 'optionsTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private Lang $lang;

    #[ORM\ManyToOne(targetEntity: Options::class, inversedBy: 'optionsTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private Options $option;

    #[ORM\ManyToOne(targetEntity: Infodocs::class, inversedBy: 'optionsTranslations')]
    private ?Infodocs $infodocs = null;

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

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setIntro(?string $intro): self
    {
        $this->intro = $intro;

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

    public function getOption(): ?Options
    {
        return $this->option;
    }

    public function setOption(?Options $option): self
    {
        $this->option = $option;

        return $this;
    }

    public function getInfodocs(): ?Infodocs
    {
        return $this->infodocs;
    }

    public function setInfodocs(?Infodocs $infodocs): self
    {
        $this->infodocs = $infodocs;

        return $this;
    }
}
