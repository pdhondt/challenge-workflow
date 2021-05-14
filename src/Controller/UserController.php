<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use JetBrains\PhpStorm\Pure;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// TODO: replace routing with something that fits an agent overview/management system better
//  ie. /agents
#[Route('/user')]
class UserController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    #[Route('/', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        // for now, we will hardcode this filter functionality
        // TODO: in hindsight, this filter function probably belongs in the $userRepository class
        //    look into it and move it if applicable.

        $filter = array('ROLE_AGENT_1', 'ROLE_AGENT_2');
        $users = $this->filterByRoles($userRepository->findAll(), $filter);

        //next, parse through the allowed users and make the display of roles a little bit nicer.
        // TODO: also, uh, get rid of the 'user' role display, that goes without saying.

        foreach($users as $user)
        {
            User::setRolesReadable($user);
        }

        return $this->render('user/index.html.twig', [
//            'users' => $userRepository->findAll(),
            'users' => $users
        ]);
    }

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {

        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            //encode password
            $user->setPassword($this->passwordEncoder->encodePassword($user,$user->getPassword()));
            //add user role
            $user->addRole('ROLE_USER');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

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
