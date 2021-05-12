<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="userCommented")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="userCreated")
     */
    private $createdTickets;

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="assignedAgent")
     */
    private $assignedTickets;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->createdTickets = new ArrayCollection();
        $this->assignedTickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
//        $roles[] = 'ROLE_USER';
        // this might not be the best idea but I'd have to ask Koen what he thinks.
        // it's probably safer to just assign the ROLE_USER when a new user is created, rather than by default.

        return array_unique($roles);
    }

    public static function setRolesReadable(User $user) : void
    {
        $roles = $user->getRoles();
        //go through roles and replace with more readable versions.
        $roles = array_map(function($role){
            return match ($role)
            {
                'ROLE_USER' => 'user',
                'ROLE_CUSTOMER' => 'customer',
                'ROLE_AGENT_1' => 'first line agent',
                'ROLE_AGENT_2' => 'second line agent',
                'ROLE_MANAGER' => 'manager',
                'ROLE_ADMIN' => 'administrator test account',
                default => $role,
            };
        }, $roles);
        $user->setRoles($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setUserCommented($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUserCommented() === $this) {
                $comment->setUserCommented(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Ticket[]
     */
    public function getCreatedTickets(): Collection
    {
        return $this->createdTickets;
    }

    public function addCreatedTicket(Ticket $createdTicket): self
    {
        if (!$this->createdTickets->contains($createdTicket)) {
            $this->createdTickets[] = $createdTicket;
            $createdTicket->setUserCreated($this);
        }

        return $this;
    }

    public function removeCreatedTicket(Ticket $createdTicket): self
    {
        if ($this->createdTickets->removeElement($createdTicket)) {
            // set the owning side to null (unless already changed)
            if ($createdTicket->getUserCreated() === $this) {
                $createdTicket->setUserCreated(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Ticket[]
     */
    public function getAssignedTickets(): Collection
    {
        return $this->assignedTickets;
    }

    public function addAssignedTicket(Ticket $assignedTicket): self
    {
        if (!$this->assignedTickets->contains($assignedTicket)) {
            $this->assignedTickets[] = $assignedTicket;
            $assignedTicket->setAssignedAgent($this);
        }

        return $this;
    }

    public function removeAssignedTicket(Ticket $assignedTicket): self
    {
        if ($this->assignedTickets->removeElement($assignedTicket)) {
            // set the owning side to null (unless already changed)
            if ($assignedTicket->getAssignedAgent() === $this) {
                $assignedTicket->setAssignedAgent(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
