<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserType;
use http\Client\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;


class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    public function register(MailerInterface $mailer,  Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $formAuthenticator)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           // /** @var User $userModel */
           // $userModel = $form->getData();
           // $user = new User();
           // $user->setFirstName($userModel->firstName);
           // $user->setEmail($userModel->email);
           // $user->setPassword($passwordEncoder->encodePassword(
           //     $user,
           //     $userModel->plainPassword
           // ));
           // // be absolutely sure they agree
           // if (true === $userModel->agreeTerms) {
           //     $user->agreeToTerms();
           // }

            $email = (new TemplatedEmail())
                ->from('kantarjiev88@abv.bg')
                ->to($user->getEmail())
                ->subject('Welcome to the Team!')
                ->text("Welcome to aware team {$user->getUsername()}!")
                ->htmlTemplate('email/welcome.html.twig')
                ->context([
                    'user' => $user
                ]);

            $mailer->send($email);

            return new Response('Email sent');
        }
    }
}
