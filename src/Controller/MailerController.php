<?php


namespace App\Controller;

use App\Entity\User;
use phpDocumentor\Reflection\Types\Context;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailerController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function sendEmail(MailerInterface $mailer ,User $user,User $customer): Response
    {
        $email = (new TemplatedEmail())
            ->from($user->getEmail())
            ->to($customer->getEmail())
            ->subject('Welcome to the Team!')
            ->text("Welcome to aware team {$customer->getUsername()}!")
            ->htmlTemplate('email/welcome.html.twig')
        ->context([
           'customer' => $customer
        ]);

        $mailer->send($email);

   return new Response('Email sent');
    }
}
