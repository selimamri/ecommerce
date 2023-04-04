<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use function password_hash;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/', name: 'homepage')]
class UserController extends AbstractController
{
    #[Route('/api', name: 'api')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }

    // #[Route('user', name: 'app_user_index', methods: ['GET'])]
    // public function index(UserRepository $userRepository): JsonResponse
    // {
    //     // return $this->render('user/index.html.twig', [
    //     //     'users' => $userRepository->findAll(),
    //     // ]);
    //     return new JsonResponse([
    //         'success' => true,
    //         'users' => $userRepository->findAll(),
    //     ]);
    // }

    #[Route('api/register', name: 'app_user_create', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasherInterface): JsonResponse
    {
        $data=json_decode($request->getContent(),true);

        $user = new User();

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
        $user->setLogin($data['login']);
        $user->setEmail($data['email']);
        $user->setPassword($hashedPassword);
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'User created successfully'
        ]);
    }

    #[Route('user/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(UserRepository $userRepository, $id): JsonResponse
    {
        $userData = $userRepository->find($id);

        if (!$userData) {
            return $this->json([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        return $this->json([
            'success' => true,
            'login' => $userData->getLogin(),
            'password' => $userData->getPassword(),
            'email' => $userData->getEmail(),
            'firstname' => $userData->getFirstname(),
            'lastname' => $userData->getLastname()
        ]);
    }

    // #[Route('user/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    // public function edit(Request $request, User $user, UserRepository $userRepository): JsonResponse
    // {
    //     $form = $this->createForm(UserType::class, $user);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $userRepository->save($user, true);

    //         return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('user/edit.html.twig', [
    //         'user' => $user,
    //         'form' => $form,
    //     ]);
    // }

    // #[Route('user/{id}', name: 'app_user_delete', methods: ['POST'])]
    // public function delete(Request $request, User $user, UserRepository $userRepository): JsonResponse
    // {
    //     if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
    //         $userRepository->remove($user, true);
    //     }

    //     return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    // }
}
