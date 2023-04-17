<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Entity\Product;
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
    public function updateOrder(Order $order, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository, $productId): JsonResponse
    {
        $productData = $productRepository->find($productId);

        # check if the user is the owner of the order
        $user = $this->getUser();
        if ($order->getUser() !== $user) {
            return $this->json([
                'success' => false,
                'message' => 'You are not the owner of this order'
            ], 403);
        }

        $data = json_decode($request->getContent(), true);

        $order->setTotalPrice($data['totalPrice']);
        $order->setIsValidate($data['isValidate']);
        $date = new \DateTime();
        $order->setCreationDate($date);
        
        $order->addProduct($productData);

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Order updated successfully'
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

        return $this->json($data, 200);
    }

}
