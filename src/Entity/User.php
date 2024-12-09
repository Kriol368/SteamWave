<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $steamID64 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $steamUsername = null;

    #[ORM\OneToMany(mappedBy: 'postUser', targetEntity: Post::class)]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Message::class)]
    private Collection $messages;

    #[ORM\ManyToMany(targetEntity: Chat::class, inversedBy: 'users')]
    private Collection $chats;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'following')]
    #[ORM\JoinTable(name: 'user_followers')]
    private Collection $followers;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'followers')]
    private Collection $following;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $banner = null;

    #[ORM\Column(length: 161, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: UserPost::class, mappedBy: 'user')]
    private Collection $userPosts;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->chats = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->userPosts = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // Clear temporary, sensitive data
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSteamID64(): ?string
    {
        return $this->steamID64;
    }

    public function setSteamID64(?string $steamID64): static
    {
        $this->steamID64 = $steamID64;

        return $this;
    }

    public function getSteamUsername(): ?string
    {
        return $this->steamUsername;
    }

    public function setSteamUsername(?string $steamUsername): static
    {
        $this->steamUsername = $steamUsername;

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Post $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setPostUser($this);
        }

        return $this;
    }

    public function removeImage(Post $image): static
    {
        if ($this->images->removeElement($image)) {
            if ($image->getPostUser() === $this) {
                $image->setPostUser(null);
            }
        }

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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setUser($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getUser() === $this) {
                $message->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Chat>
     */
    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function addChat(Chat $chat): static
    {
        if (!$this->chats->contains($chat)) {
            $this->chats->add($chat);
        }

        return $this;
    }

    public function removeChat(Chat $chat): static
    {
        $this->chats->removeElement($chat);

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(self $user): static
    {
        if (!$this->followers->contains($user)) {
            $this->followers->add($user);
            $user->addFollowing($this); // Add reciprocal relation
        }

        return $this;
    }

    public function removeFollower(self $user): static
    {
        if ($this->followers->removeElement($user)) {
            $user->removeFollowing($this); // Remove reciprocal relation
        }

        return $this;
    }


    /**
     * @return Collection<int, self>
     */
    public function getFollowing(): Collection
    {
        return $this->following;
    }


    public function addFollowing(self $user): static
    {
        if (!$this->following->contains($user)) {
            $this->following->add($user);
            $user->addFollower($this); // Add reciprocal relation
        }

        return $this;
    }

    public function removeFollowing(self $user): static
    {
        if ($this->following->removeElement($user)) {
            $user->removeFollower($this); // Remove reciprocal relation
        }

        return $this;
    }

    public function getBanner(): ?string
    {
        return $this->banner;
    }

    public function setBanner(?string $banner): static
    {
        $this->banner = $banner;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
            $userPost->setUser($this);
        }

        return $this;
    }

    public function removeUserPost(UserPost $userPost): static
    {
        if ($this->userPosts->removeElement($userPost)) {
            // set the owning side to null (unless already changed)
            if ($userPost->getUser() === $this) {
                $userPost->setUser(null);
            }
        }

        return $this;
    }
}
