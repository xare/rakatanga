<?php

namespace App\Entity;

use App\Repository\InfodocsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Service\UploadHelper;

/**
 * @ORM\Entity(repositoryClass=InfodocsRepository::class)
 */
class Infodocs
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
    private $filename;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $original_filename;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mime_type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\ManyToMany(targetEntity=Travel::class, inversedBy="infodocs")
     */
    private $travel;

    /**
     * @ORM\OneToMany(targetEntity=OptionsTranslations::class, mappedBy="infodocs")
     */
    private $optionsTranslations;

    public function __construct()
    {
        $this->travel = new ArrayCollection();
        $this->optionsTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->original_filename;
    }

    public function setOriginalFilename(string $original_filename): self
    {
        $this->original_filename = $original_filename;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mime_type;
    }

    public function setMimeType(?string $mime_type): self
    {
        $this->mime_type = $mime_type;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
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
    public function getPath()
    {
        return  UploadHelper::INFODOCS . '/' . $this->getFilename();
    }
    public function getInfodocsPath()
    {
        return  '/uploads/' . UploadHelper::INFODOCS . '/' . $this->getFilename();
    }
    /**
     * @return Collection|Travel[]
     */
    public function getTravel(): Collection
    {
        return $this->travel;
    }

    public function addTravel(Travel $travel): self
    {
        if (!$this->travel->contains($travel)) {
            $this->travel[] = $travel;
        }

        return $this;
    }

    public function removeTravel(Travel $travel): self
    {
        $this->travel->removeElement($travel);

        return $this;
    }

    /**
     * @return Collection|OptionsTranslations[]
     */
    public function getOptionsTranslations(): Collection
    {
        return $this->optionsTranslations;
    }

    public function addOptionsTranslation(OptionsTranslations $optionsTranslation): self
    {
        if (!$this->optionsTranslations->contains($optionsTranslation)) {
            $this->optionsTranslations[] = $optionsTranslation;
            $optionsTranslation->setInfodocs($this);
        }

        return $this;
    }

    public function removeOptionsTranslation(OptionsTranslations $optionsTranslation): self
    {
        if ($this->optionsTranslations->removeElement($optionsTranslation)) {
            // set the owning side to null (unless already changed)
            if ($optionsTranslation->getInfodocs() === $this) {
                $optionsTranslation->setInfodocs(null);
            }
        }

        return $this;
    }
}
