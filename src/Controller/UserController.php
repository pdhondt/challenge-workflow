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

// TODO: replace routing with something that fits an agent overview/management system better
//  ie. /agents
#[Route('/users')]
class UserController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    #[Route('/', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository,): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        // for now, we will hardcode this filter functionality
        // TODO: in hindsight, this filter function probably belongs in the $userRepository class
        //    look into it and move it if applicable.

        $filter = array('ROLE_AGENT_1', 'ROLE_AGENT_2');
        $users = $this->filterByRoles($userRepository->findAll(), $filter);

        //next, parse through the allowed users and make the display of roles a little bit nicer.
        foreach ($users as $user) User::setRolesReadable($user);

        return $this->render('user/index.html.twig', [
//            'users' => $userRepository->findAll(),
            'users' => $users
        ]);
    }

    #[Route('/stats', name: 'user_stats', methods: ['GET'])]
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

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MailerInterface $mailer): Response
    {

        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            //encode password
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
            //add user role
            $user->addRole('ROLE_USER');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // TODO: compose a proper welcome e-mail to a new agent.
            //   also, maybe split this off.

            $email = (new Email())
                ->from('helpdesk@someplace.net')
                ->to('me@test.mail')
                ->subject('account activation!')
                ->text('guess what chuckle****, you work for us now!');

            $mailer->send($email);

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token')))
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    #region Utilities

    /**
     * @param array $users array of users to be filtered by this method
     * @param array $roles array of roles to be filtered by this method
     * @param bool $filterOutRoles if false, it will remove any user who does not have one of the roles in the roles filter array. if true, it will remove the ones in the filter array.
     * @return array
     */
    #[Pure] private function filterByRoles(array $users, array $roles, bool $filterOutRoles = false): array
    {
        // this function will check the array users by role
        $filteredUsers = [];
        foreach ($users as $user)
        {
            if ($user instanceof User)
            {
                if ($filterOutRoles === empty(array_intersect($user->getRoles(), $roles)))
                {
                    $filteredUsers[] = $user;
                    continue;
                }
            }
        }
        return $filteredUsers;
    }

    #endregion
}
