<?php


namespace App\Utility;


use App\Entity\User;

class AgentStatistics
{
    private $user;
    private $openTickets;
    private $closedTickets;
    private $reopenedTickets;


    public function __construct(User $user)
    {
        $this->user = $user;
    }
    #region GettersSetters
    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getOpenTickets(): int
    {
        return $this->openTickets;
    }

    public function setOpenTickets(int $openTickets): void
    {
        $this->openTickets = $openTickets;
    }

    public function getClosedTickets(): int
    {
        return $this->closedTickets;
    }

    public function setClosedTickets(int $closedTickets): void
    {
        $this->closedTickets = $closedTickets;
    }

    public function getReopenedTickets(): int
    {
        return $this->reopenedTickets;
    }

    public function setReopenedTickets(int $reopenedTickets): void
    {
        $this->reopenedTickets = $reopenedTickets;
    }
    #endregion

    public function getPercentageRatio() : float
    {
        if($this->getReopenedTickets() <= 0)
        {
            return 0;
        }
        return $this->getClosedTickets()/$this->getReopenedTickets() * 100;
    }


}