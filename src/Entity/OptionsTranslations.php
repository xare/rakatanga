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

    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private $intro;

    #[ORM\ManyToOne(targetEntity: Lang::class, inversedBy: 'optionsTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private $lang;

    #[ORM\ManyToOne(targetEntity: Options::class, inversedBy: 'optionsTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private $options;

    #[ORM\ManyToOne(targetEntity: Infodocs::class, inversedBy: 'optionsTranslations')]
    private $infodocs;

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

    public function getOptions(): ?Options
    {
        return $this->options;
    }

    public function setOptions(?Options $options): self
    {
        $this->options = $options;

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
