<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TicketRepository::class)
 */
class Ticket
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isEscalated;

    /**
     * @ORM\Column(type="integer")
     */
    private $priority;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPrivate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isReopened;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closed;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="ticket", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity=Status::class, inversedBy="ticket")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="createdTickets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userCreated;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="assignedTickets")
     */
    private $assignedAgent;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsEscalated(): ?bool
    {
        return $this->isEscalated;
    }

    public function setIsEscalated(bool $isEscalated): self
    {
        $this->isEscalated = $isEscalated;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getIsPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function getIsReopened(): ?bool
    {
        return $this->isReopened;
    }

    public function setIsReopened(bool $isReopened): self
    {
        $this->isReopened = $isReopened;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getClosed(): ?\DateTimeInterface
    {
        return $this->closed;
    }

    public function setClosed(?\DateTimeInterface $closed): self
    {
        $this->closed = $closed;

        return $this;
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
            $comment->setTicket($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTicket() === $this) {
                $comment->setTicket(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUserCreated(): ?User
    {
        return $this->userCreated;
    }

    public function setUserCreated(?User $userCreated): self
    {
        $this->userCreated = $userCreated;

        return $this;
    }

    public function getAssignedAgent(): ?User
    {
        return $this->assignedAgent;
    }

    public function setAssignedAgent(?User $assignedAgent): self
    {
        $this->assignedAgent = $assignedAgent;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
