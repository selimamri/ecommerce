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

    # UPDATE AN ORDER
    #[Route('/api/carts/{productId}', name: 'app_order_update', methods: ['PUT'])]
    public function updateOrder(OrderRepository $orderRepository, ProductRepository $productRepository, EntityManagerInterface $entityManager, $productId): JsonResponse
    {
        $product = $productRepository->find($productId);
        $user = $this->getUser();
        
        $order = $orderRepository->findOneBy(['user' => $user, 'isValidate' => false]);
        
        if (!$order) {
            $order = new Order();
            $order->setUser($user);
            $entityManager->persist($order);
        }
        
        $order->addProduct($product);
        $order->setTotalPrice($order->getTotalPrice() + $product->getPrice());
        
        $entityManager->flush();
        
        return $this->json([
            'success' => true,
            'message' => 'Product added to cart successfully'
        ], 200);
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

    # VALIDATE AN ORDER
    #[Route('/api/carts/validate', name: 'app_order_validate', methods: ['PUT'])]
    public function validateOrder(OrderRepository $orderRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $orderData = $orderRepository->findOneBy(['user' => $user, 'isValidate' => false]);

        $orderData->setIsValidate(true);
        $order = new Order();
        $order->setTotalPrice(0);
        $order->setUser($user);
        $order->setIsValidate(false);
        $date = new \DateTime();
        $order->setCreationDate($date);

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Order Validated'
        ], 200);
    }

    # DELETE A PRODUCT FROM AN ORDER
    #[Route('/api/carts/{productId}', name: 'app_order_delete', methods: ['DELETE'])]
    public function deleteProduct(OrderRepository $orderRepository, ProductRepository $productRepository, EntityManagerInterface $entityManager, $productId): JsonResponse
    {
        $product = $productRepository->find($productId);
        $user = $this->getUser();
        $order = $orderRepository->findOneBy(['user' => $user, 'isValidate' => false]);

        $order->removeProduct($product);
        $order->setTotalPrice($order->getTotalPrice() - $product->getPrice());

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Product deleted from cart successfully'
        ], 200);
    }
}
