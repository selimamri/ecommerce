<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use function password_hash;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Order;
use App\Repository\OrderRepository;

#[Route('/', name: 'homepage')]
class UserController extends AbstractController
{
    #[Route('/api', name: 'api')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiController.php',
        ], 200);
    }

    # CREATE A USER
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

        # create a new order
        $order = new Order();
        $order->setTotalPrice(0);
        $order->setUser($user);
        $order->setIsValidate(false);
        $date = new \DateTime();
        $order->setCreationDate($date);
        
        $entityManager->persist($user);
        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'error' => 'User and Order created successfully'
        ], 201);

        if (empty($data['login'])) {
            return $this->json([
                'success' => false,
                'error' => 'Missing login field'
            ], 400);
        }
        if (empty($data['email'])) {
            return $this->json([
                'success' => false,
                'error' => 'Missing email field'
            ], 400);
        }
        if (empty($data['password'])) {
            return $this->json([
                'success' => false,
                'error' => 'Missing password field'
            ], 400);
        }
        if (empty($data['firstname'])) {
            return $this->json([
                'success' => false,
                'error' => 'Missing firstname field'
            ], 400);
        }
        if (empty($data['lastname'])) {
            return $this->json([
                'success' => false,
                'error' => 'Missing lastname field'
            ], 400);
        }
    }

    # DISPLAY USER INFORMATION
    #[Route('api/user/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(UserRepository $userRepository, $id): JsonResponse
    {
        $userData = $userRepository->find($id);

        if (!$userData) {
            return $this->json([
                'success' => false,
                'error' => 'User not found'
            ], 404);
        }

        return $this->json($userData, 200,[],['groups' => ['user']]);
    }

    # DISPLAY ALL USERS
    #[Route('api/users', name: 'app_users_list', methods: ['GET'])]
    public function list(UserRepository $userRepository): JsonResponse
    {
        $userData = $userRepository->findAll();

        if (!$userData) {
            return $this->json([
                'success' => false,
                'error' => 'Users not found'
            ], 404);
        }

        return $this->json($userData, 200,[],['groups' => ['user']]);
    }

    # UPDATE USER INFORMATION
    #[Route('api/users/{id}', name: 'app_user_update', methods: ['PUT'])]
    public function update(Request $request, UserRepository $userRepository, $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $userData = $userRepository->find($id);

        $data = json_decode($request->getContent(), true);

        if (!$userData) {
            return $this->json([
                'success' => false,
                'error' => 'User not found'
            ], 404);
        }

        // $login = $request->request->get('login');
        // if (!empty($login)) {
        //     $userData->setLogin($login);
        // }

        // $userData->setLogin($request->get('login'));
        // $userData->setPassword($request->get('password'));
        $userData->setEmail($data['email']);
        $userData->setFirstname($data['firstname']);
        $userData->setLastname($data['lastname']);

        $entityManager->persist($userData);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $userData
        ], 200);
    }
}
