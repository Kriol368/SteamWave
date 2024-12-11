<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'posts')]
    private ?User $postUser = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $numLikes = null;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post')]
    private Collection $comments;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tag = null;

    #[ORM\OneToMany(targetEntity: UserPost::class, mappedBy: 'post')]
    private Collection $userPosts;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->userPosts = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    public function getPostUser(): ?User
    {
        return $this->postUser;
    }

    public function setPostUser(?User $postUser): static
    {
        $this->postUser = $postUser;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getNumLikes(): ?int
    {
        return $this->numLikes;
    }

    public function setNumLikes(int $numLikes): static
    {
        $this->numLikes = $numLikes;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(?string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return Collection<int, UserPost>
     */
    public function getUserPosts(): Collection
    {
        return $this->userPosts;
    }

    public function addUserPost(UserPost $userPost): static
    {
        if (!$this->userPosts->contains($userPost)) {
            $this->userPosts->add($userPost);
            $userPost->setPost($this);
        }

        return $this;
    }

    public function removeUserPost(UserPost $userPost): static
    {
        if ($this->userPosts->removeElement($userPost)) {
            // set the owning side to null (unless already changed)
            if ($userPost->getPost() === $this) {
                $userPost->setPost(null);
            }
        }

        return $this;
    }


}
