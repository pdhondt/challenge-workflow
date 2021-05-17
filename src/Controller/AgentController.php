<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Utility\AgentStatistics;
use JetBrains\PhpStorm\Pure;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/agents')]
class AgentController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    #[Route('/', name: 'agent_index', methods: ['GET'])]
    public function index(UserRepository $userRepository,): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        // for now, we will hardcode this filter functionality
        // TODO: in hindsight, this filter function probably belongs in the $userRepository class
        //    look into it and move it if applicable.

        $filter = array('ROLE_AGENT_1', 'ROLE_AGENT_2');
        $agents = $this->filterByRoles($userRepository->findAll(), $filter);

        //next, parse through the allowed agents and make the display of roles a little bit nicer.
        foreach ($agents as $agent) User::setRolesReadable($agent);

        return $this->render('user/index.html.twig', [
//            'agents' => $userRepository->findAll(),
            'agents' => $agents
        ]);
    }

    #[Route('/stats', name: 'agent_stats', methods: ['GET'])]
    public function StatOverview(UserRepository $userRepository, ticketRepository $ticketRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER', null, 'This page cannot be seen without proper authorization!!!');
        $filter = array('ROLE_AGENT_1', 'ROLE_AGENT_2');
        $users = $this->filterByRoles($userRepository->findAll(), $filter);

        $agentStats = [];
        foreach ($users as $user)
        {
            User::setRolesReadable($user);

            //find all tickets assigned to this agent
            $tickets = $ticketRepository->findBy([
                'assignedAgent' => $user->getId(),
            ]);

            //parse through and count tickets
            $open = 0;
            $closed = 0;
            $reopened = 0;

            foreach($tickets as $ticket)
            {
                if($ticket->getStatus()->getDescriptor() !== null)
                    switch($ticket->getStatus()->getDescriptor())
                    {
                        case('open'):
                            $open++;
                            break;
                        case('closed'):
                            $closed++;
                            break;
                        default:
                            break;
                    }

                if($ticket->getIsReopened())
                {
                    $reopened++;
                }
            }

            //stick it in a new AgentStatistics object
            $agentStatistics = new AgentStatistics($user);
            $agentStatistics->setOpenTickets($open);
            $agentStatistics->setClosedTickets($closed);
            $agentStatistics->setReopenedTickets($reopened);

            $agentStats[] = $agentStatistics;

        }
        return $this->render('user/statistics.html.twig', [
            'agents' => $agentStats,
        ]);
    }

    #[Route('/new', name: 'agent_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MailerInterface $mailer): Response
    {

        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $agent = new User();
        $form = $this->createForm(UserType::class, $agent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            //encode password
            $agent->setPassword($this->passwordEncoder->encodePassword($agent, $agent->getPassword()));
            //add agent role
            $agent->addRole('ROLE_USER');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($agent);
            $entityManager->flush();

            // TODO: compose a proper welcome e-mail to a new agent.
            //   also, maybe split this off.

            $email = (new Email())
                ->from('helpdesk@someplace.net')
                ->to('me@test.mail')
                ->subject('account activation!')
                ->text('guess what chuckle****, you work for us now!');

            $mailer->send($email);

            return $this->redirectToRoute('agent_index');
        }

        return $this->render('user/new.html.twig', [
            'agent' => $agent,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'agent_show', methods: ['GET'])]
    public function show(User $agent): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        return $this->render('user/show.html.twig', [
            'agent' => $agent,
        ]);
    }

    #[Route('/{id}/edit', name: 'agent_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $agent): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $form = $this->createForm(UserType::class, $agent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $agent->addRole('ROLE_USER');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('agent_index');
        }

        return $this->render('user/edit.html.twig', [
            'agent' => $agent,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'agent_delete', methods: ['POST'])]
    public function delete(Request $request, User $agent): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        if ($this->isCsrfTokenValid('delete' . $agent->getId(), $request->request->get('_token')))
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($agent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('agent_index');
    }

    #region Utilities

    /**
     * @param array $agents array of agents to be filtered by this method
     * @param array $roles array of roles to be filtered by this method
     * @param bool $filterOutRoles if false, it will remove any agent who does not have one of the roles in the roles filter array. if true, it will remove the ones in the filter array.
     * @return array
     */
    #[Pure] private function filterByRoles(array $agents, array $roles, bool $filterOutRoles = false): array
    {
        // this function will check the array agents by role
        $filteredUsers = [];
        foreach ($agents as $agent)
        {
            if ($agent instanceof User)
            {
                if ($filterOutRoles === empty(array_intersect($agent->getRoles(), $roles)))
                {
                    $filteredUsers[] = $agent;
                    continue;
                }
            }
        }
        return $filteredUsers;
    }

    #endregion
}
