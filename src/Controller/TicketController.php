<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

#[Route('/ticket')]
class TicketController extends AbstractController
{
    #[Route('/', name: 'ticket_index', methods: ['GET'])]
    public function index(TicketRepository $ticketRepository): Response
    {
        return $this->render('ticket/index.html.twig', [
            'tickets' => $ticketRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'ticket_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $status = $this->getDoctrine()->getRepository(Status::class)->find(1);

        /** @var User $user */
        //$user = $this->getUser();
        $userCreated = $this->getUser();

        $ticket = new Ticket();
        $ticket->setCreated(date_create('now'));
        $ticket->setStatus($status);
        $ticket->setUserCreated($userCreated);
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'ticket_show', methods: ['GET'])]
    public function show(Ticket $ticket): Response
    {
        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/{id}/edit', name: 'ticket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ticket $ticket): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'ticket_delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ticket_index');
    }

    #[Route('/{id}/reopen', name: 'ticket_reopen', methods: ['GET', 'POST'])]
    public function reopen(Ticket $ticket): Response
    {
        $status = $this->getDoctrine()->getRepository(Status::class)->find(1);
        $currentTime = new DateTime();
        $closedTime = $ticket->getClosed();
        $timeSinceClosed = $currentTime->diff($closedTime);

        if ($timeSinceClosed->i < 60)
        {
            $ticket->setStatus($status);
        }

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/{id}/close', name: 'ticket_close', methods: ['GET', 'POST'])]
    public function close(Ticket $ticket): Response
    {
        $status = $this->getDoctrine()->getRepository(Status::class)->find(2);
        $ticket->setStatus($status);
        $ticket->setClosed(date_create('now'));

        return $this->redirectToRoute('ticket_index');
    }
}
