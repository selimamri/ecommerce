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

    # ADD A PRODUCT
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

    # RETRIEVE LIST OF PRODUCTS
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

    # RETRIEVE INFORMATION ON A SPECIFIC PRODUCT
    #[Route('api/products/{productId}', name: 'app_product_show', methods: ['GET'])]
    public function show(ProductRepository $productRepository, $productId): JsonResponse
    {
        $productData = $productRepository->find($productId);

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

    # MODIFY AND DELETE A PRODUCT
    #[Route('api/products/{productId}', name: 'app_product_update', methods: ['PUT', 'DELETE'])]
    public function update(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager, $productId): JsonResponse
    {
        $productData = $productRepository->find($productId);

        if (!$productData) {
            return $this->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->getMethod() == 'PUT') {
            $productData->setName($data['name']);
            $productData->setDescription($data['description']);
            $productData->setPhoto($data['photo']);
            $productData->setPrice($data['price']);

            $entityManager->persist($productData);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Product updated successfully'
            ], 200);
        } else if ($request->getMethod() == 'DELETE') {
            $entityManager->remove($productData);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ], 200);
        }
    }
}
