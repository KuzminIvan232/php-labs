<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    private const PRODUCTS = [
        [
            'id' => '1',
            'name' => 'Keyboard',
            'description' => 'Mechanical keyboard with blue switches',
            'price' => 49.99,
        ],
        [
            'id' => '2',
            'name' => 'Mouse',
            'description' => 'Wireless ergonomic mouse',
            'price' => 19.99,
        ],
        [
            'id' => '3',
            'name' => 'Monitor',
            'description' => '27-inch 4K monitor',
            'price' => 299.99,
        ],
    ];

    /**
     * @return JsonResponse
     */
    #[Route('/products', name: 'get_products', methods: [Request::METHOD_GET])]
    public function getProducts(): JsonResponse
    {
        return new JsonResponse(['data' => self::PRODUCTS], status: Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    #[Route('/products/{id}', name: 'get_product_item', methods: [Request::METHOD_GET])]
    public function getProductItem(string $id): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return new JsonResponse(['data' => ['error' => 'Not found product by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $product], status: Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/products', name: 'post_products', methods: [Request::METHOD_POST])]
    public function createProduct(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $productId = (string) random_int(1, 100);

        $newProductData = [
            'id' => $productId,
            'name' => $requestData['name'],
            'description' => $requestData['description'],
            'price' => $requestData['price'],
        ];

        // TODO insert to db

        return new JsonResponse(['data' => $newProductData], status: Response::HTTP_CREATED);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/products/{id}', name: 'update_product', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function updateProduct(string $id, Request $request): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return new JsonResponse(['data' => ['error' => 'Not found product by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        $updatedProductData = [
            'id' => $product['id'],
            'name' => $requestData['name'] ?? $product['name'],
            'description' => $requestData['description'] ?? $product['description'],
            'price' => $requestData['price'] ?? $product['price'],
        ];

        // TODO update in db

        return new JsonResponse(['data' => $updatedProductData], status: Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    #[Route('/products/{id}', name: 'delete_product', methods: [Request::METHOD_DELETE])]
    public function deleteProduct(string $id): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return new JsonResponse(['data' => ['error' => 'Not found product by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        // TODO delete from db

        return new JsonResponse(['data' => ['message' => 'Product ' . $id . ' deleted']], status: Response::HTTP_OK);
    }

    /**
     * @param array $products
     * @param string $id
     * @return array|null
     */
    private function getProductItemById(array $products, string $id): ?array
    {
        foreach ($products as $product) {
            if ((string) $product['id'] === $id) {
                return $product;
            }
        }

        return null;
    }
}
