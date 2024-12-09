<?php

namespace App\Entity;

use App\Repository\UserPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPostRepository::class)]
#[ORM\Table(name: 'user_post')]
class UserPost
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userPosts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'userPosts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $saved = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $liked = null;

    // Getters and Setters
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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

    public function isSaved(): ?bool
    {
        return $this->saved;
    }

    public function setSaved(?bool $saved): static
    {
        $this->saved = $saved;
        return $this;
    }

    public function isLiked(): ?bool
    {
        return $this->liked;
    }

    public function setLiked(?bool $liked): static
    {
        $this->liked = $liked;
        return $this;
    }
}
