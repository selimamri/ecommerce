<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;


class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(): Response
    {
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }

//    # UPDATE AN ORDER
//     #[Route('/api/carts/{productId}', name: 'app_order_update', methods: ['PUT'])]
//     public function updateOrder(Order $order, OrderRepository $orderRepository, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository, $productId): JsonResponse
//     {
//         $product = $productRepository->find($productId);
//         $user = $this->getUser();
//         # search the order of the user
//         $order = $orderRepository->findOneBy(['user' => $user, 'isValidate' => false]);
       
//         if ($order->getUser() !== $user) {
//             return $this->json([
//                 'success' => false,
//                 'message' => 'You are not the owner of this order'
//             ], 403);
//         }

//         $data = json_decode($request->getContent(), true);

//         // $order->setIsValidate(false);
//         $date = new \DateTime();
//         $order->setCreationDate($date);
        
//         $order->addProduct($product);
//         $order->setTotalPrice($order->getTotalPrice() + $product->getPrice());

//         // $entityManager->persist($order);
//         $entityManager->flush();

//         return $this->json([
//             'success' => true,
//             'message' => 'Order updated successfully'
//         ], 200);
//     }

    #[Route('/api/carts/{productId}', name: 'app_order_update', methods: ['PUT'])]
    public function updateOrder(OrderRepository $orderRepository, ProductRepository $productRepository, EntityManagerInterface $entityManager, Request $request, $productId): JsonResponse
    {
        $product = $productRepository->find($productId);
        $user = $this->getUser();
        
        // Récupérer la commande en cours de l'utilisateur
        $order = $orderRepository->findOneBy(['user' => $user, 'isValidate' => false]);
        
        if (!$order) {
            // Si l'utilisateur n'a pas de commande en cours, créer une nouvelle commande
            $order = new Order();
            $order->setUser($user);
            $entityManager->persist($order);
        }
        
        // Ajouter le produit à la commande
        $order->addProduct($product);
        $order->setTotalPrice($order->getTotalPrice() + $product->getPrice());
        
        $entityManager->flush();
        
        return $this->json([
            'success' => true,
            'message' => 'Product added to cart successfully'
        ]);
    }


    # GET ALL ORDERS OF A USER
    #[Route('/api/carts', name: 'app_order_get', methods: ['GET'])]
    public function getOrders(OrderRepository $orderRepository): JsonResponse
    {
        $user = $this->getUser();
        $orders = $orderRepository->findBy(['user' => $user]);

        $data = [];

        foreach ($orders as $order) {
            $data[] = [
                'id' => $order->getId(),
                'totalPrice' => $order->getTotalPrice(),
                'creationDate' => $order->getCreationDate(),
                'isValidate' => $order->isValidate(),
                'products' => $order->getProducts()
            ];
        }

        return $this->json($data, 200,[],['groups' => ['order']]);
    }

}
