<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }

    # CREATE A PRODUCT
    #[Route('api/products', name: 'app_product_create', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data=json_decode($request->getContent(),true);
        $product = new Product();

        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPhoto($data['photo']);
        $product->setPrice($data['price']);

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Product created successfully'
        ], 201);
    }

    # DISPLAY ALL PRODUCTS
    #[Route('api/products', name: 'app_product_list', methods: ['GET'])]
    public function list(ProductRepository $productRepository): JsonResponse
    {
        $productData = $productRepository->findAll();

        if (!$productData) {
            return $this->json([
                'success' => false,
                'message' => 'No product found'
            ], 404);
        }

        return $this->json([
            'success' => true,
            'message' => 'Product found',
            'data' => $productData
        ], 200);
    }

    # DISPLAY PRODUCT INFORMATION
    #[Route('api/products/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(ProductRepository $productRepository, $id): JsonResponse
    {
        $productData = $productRepository->find($id);

        if (!$productData) {
            return $this->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        } else {
            return $this->json([
                'success' => true,
                'message' => 'Product found',
                'data' => $productData
            ], 200);
        }
    }
}
