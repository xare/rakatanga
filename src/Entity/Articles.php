<?php

namespace App\Entity;

use App\Repository\ArticlesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticlesRepository::class)]
class Articles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column()]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $intro;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $body;

    #[ORM\ManyToMany(targetEntity: Media::class, inversedBy: 'articles')]
    private Collection $media;

    #[ORM\Column()]
    private \DateTimeImmutable $publishedAt;

    #[ORM\ManyToOne(targetEntity: Blog::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private Blog $blog;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $slug = null;

    #[ORM\OneToOne(targetEntity: Media::class, inversedBy: 'article', cascade: ['persist', 'remove'])]
    private ?Media $mainPhoto = null;

    public function __construct()
    {
        $this->publishedAt = new \DateTimeImmutable();
        $this->media = new ArrayCollection();
    }

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

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
        }

        return $this;
    }

    public function removeMedium(Media $medium): self
    {
        $this->media->removeElement($medium);

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    /* public function setPublishedAt(\DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    } */

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function setBlog(?Blog $blog): self
    {
        $this->blog = $blog;

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

    public function getMainPhoto(): ?Media
    {
        return $this->mainPhoto;
    }

    public function setMainPhoto(?Media $mainPhoto): self
    {
        $this->mainPhoto = $mainPhoto;

        return $this;
    }
}
