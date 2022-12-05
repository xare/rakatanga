<?php

namespace App\Entity;

use App\Repository\TravelTranslationRepository;
use App\Service\slugifyHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TravelTranslationRepository::class)
 */
class TravelTranslation
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
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity=Lang::class, inversedBy="travelTranslations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lang;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $summary;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $intro;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $itinerary;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $practical_info;

    /**
     * @ORM\ManyToOne(targetEntity=Travel::class, inversedBy="travelTranslation", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $travel;

   
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



    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getItinerary(): ?string
    {
        return $this->itinerary;
    }

    public function setItinerary(?string $itinerary): self
    {
        $this->itinerary = $itinerary;

        return $this;
    }

    public function getPracticalInfo(): ?string
    {
        return $this->practical_info;
    }

    public function setPracticalInfo(?string $practical_info): self
    {
        $this->practical_info = $practical_info;

        return $this;
    }

    public function getTravel(): ?Travel
    {
        return $this->travel;
    }

    public function setTravel(?Travel $travel): self
    {
        $this->travel = $travel;

        return $this;
    }

    public function __toString(){
        return $this->getTitle();
    }
}
