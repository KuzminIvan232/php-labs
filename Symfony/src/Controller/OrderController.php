<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\CustomerRepository;
use App\Repository\MenuItemRepository;
use App\Repository\OrderRepository;
use App\Repository\RestaurantTableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/orders', name: 'get_orders', methods: [Request::METHOD_GET])]
    public function getOrders(OrderRepository $orderRepository): JsonResponse
    {
        $orders = array_map(
            static fn (Order $order) => $order->toArray(),
            $orderRepository->findAll()
        );

        return new JsonResponse(['data' => $orders], status: Response::HTTP_OK);
    }

    #[Route('/orders/{id}', name: 'get_order_item', methods: [Request::METHOD_GET])]
    public function getOrderItem(int $id, OrderRepository $orderRepository): JsonResponse
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            return new JsonResponse(['data' => ['error' => 'Not found order by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $order->toArray()], status: Response::HTTP_OK);
    }

    /**
     * Creates an order together with its order items in one request, e.g.:
     * {
     *   "customerId": 1,
     *   "restaurantTableId": 2,
     *   "items": [{"menuItemId": 3, "quantity": 2}, {"menuItemId": 5, "quantity": 1}]
     * }
     */
    #[Route('/orders', name: 'post_orders', methods: [Request::METHOD_POST])]
    public function createOrder(
        Request $request,
        CustomerRepository $customerRepository,
        RestaurantTableRepository $tableRepository,
        MenuItemRepository $menuItemRepository
    ): JsonResponse {
        $requestData = json_decode($request->getContent(), associative: true);

        $customer = $customerRepository->find($requestData['customerId']);
        if (!$customer) {
            return new JsonResponse(['data' => ['error' => 'Not found customer by id ' . $requestData['customerId']]], status: Response::HTTP_NOT_FOUND);
        }

        $table = null;
        if (!empty($requestData['restaurantTableId'])) {
            $table = $tableRepository->find($requestData['restaurantTableId']);
            if (!$table) {
                return new JsonResponse(['data' => ['error' => 'Not found table by id ' . $requestData['restaurantTableId']]], status: Response::HTTP_NOT_FOUND);
            }
        }

        $order = new Order();
        $order->setCustomer($customer);
        $order->setRestaurantTable($table);
        $order->setStatus($requestData['status'] ?? 'new');

        foreach ($requestData['items'] ?? [] as $itemData) {
            $menuItem = $menuItemRepository->find($itemData['menuItemId']);
            if (!$menuItem) {
                return new JsonResponse(['data' => ['error' => 'Not found menu item by id ' . $itemData['menuItemId']]], status: Response::HTTP_NOT_FOUND);
            }

            $orderItem = new OrderItem();
            $orderItem->setMenuItem($menuItem);
            $orderItem->setQuantity($itemData['quantity']);
            $orderItem->setUnitPrice((string) $menuItem->getPrice());

            $order->addOrderItem($orderItem);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $order->toArray()], status: Response::HTTP_CREATED);
    }

    #[Route('/orders/{id}', name: 'update_order', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function updateOrder(
        int $id,
        Request $request,
        OrderRepository $orderRepository,
        RestaurantTableRepository $tableRepository
    ): JsonResponse {
        $order = $orderRepository->find($id);

        if (!$order) {
            return new JsonResponse(['data' => ['error' => 'Not found order by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        if (array_key_exists('restaurantTableId', $requestData)) {
            $table = $requestData['restaurantTableId'] ? $tableRepository->find($requestData['restaurantTableId']) : null;
            if ($requestData['restaurantTableId'] && !$table) {
                return new JsonResponse(['data' => ['error' => 'Not found table by id ' . $requestData['restaurantTableId']]], status: Response::HTTP_NOT_FOUND);
            }
            $order->setRestaurantTable($table);
        }

        $order->setStatus($requestData['status'] ?? $order->getStatus());

        $this->entityManager->flush();

        return new JsonResponse(['data' => $order->toArray()], status: Response::HTTP_OK);
    }

    #[Route('/orders/{id}', name: 'delete_order', methods: [Request::METHOD_DELETE])]
    public function deleteOrder(int $id, OrderRepository $orderRepository): JsonResponse
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            return new JsonResponse(['data' => ['error' => 'Not found order by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($order);
        $this->entityManager->flush();

        return new JsonResponse(['data' => ['message' => 'Order ' . $id . ' deleted']], status: Response::HTTP_OK);
    }
}
