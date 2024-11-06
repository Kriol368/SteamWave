<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Post $post = null;

    #[ORM\ManyToOne(inversedBy: 'isChildComment')]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $isChildComment = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'comments')]
    private ?self $parentComment = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentComment')]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeInterface $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isIsChildComment(): ?bool
    {
        return $this->isChildComment;
    }

    public function setIsChildComment(bool $isChildComment): static
    {
        $this->isChildComment = $isChildComment;

        return $this;
    }

    public function getParentComment(): ?self
    {
        return $this->parentComment;
    }

    public function setParentComment(?self $parentComment): static
    {
        $this->parentComment = $parentComment;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(self $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setParentComment($this);
        }

        return $this;
    }

    public function removeComment(self $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getParentComment() === $this) {
                $comment->setParentComment(null);
            }
        }

        return $this;
    }
}
