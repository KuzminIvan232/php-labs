<?php

namespace App\Controller;

use App\Entity\OrderItem;
use App\Repository\MenuItemRepository;
use App\Repository\OrderItemRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Lab 5: direct order-line management (outside the nested POST /orders flow)
// is staff-only across the board — every action here requires Manager/Admin.
#[IsGranted('ROLE_MANAGER')]
class OrderItemController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/order-items', name: 'get_order_items', methods: [Request::METHOD_GET])]
    public function getOrderItems(Request $request, OrderItemRepository $orderItemRepository): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $itemsPerPage = max(1, (int) $request->query->get('itemsPerPage', 10));

        $result = $orderItemRepository->search($request->query->all(), $page, $itemsPerPage);

        return new JsonResponse([
            'data' => array_map(static fn (OrderItem $orderItem) => $orderItem->toArray(), $result['items']),
            'meta' => $result['meta'],
        ], status: Response::HTTP_OK);
    }

    #[Route('/order-items/{id}', name: 'get_order_item_item', methods: [Request::METHOD_GET])]
    public function getOrderItemItem(int $id, OrderItemRepository $orderItemRepository): JsonResponse
    {
        $orderItem = $orderItemRepository->find($id);

        if (!$orderItem) {
            return new JsonResponse(['data' => ['error' => 'Not found order item by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $orderItem->toArray()], status: Response::HTTP_OK);
    }

    #[Route('/order-items', name: 'post_order_items', methods: [Request::METHOD_POST])]
    public function createOrderItem(
        Request $request,
        OrderRepository $orderRepository,
        MenuItemRepository $menuItemRepository
    ): JsonResponse {
        $requestData = json_decode($request->getContent(), associative: true);

        $order = $orderRepository->find($requestData['orderId']);
        if (!$order) {
            return new JsonResponse(['data' => ['error' => 'Not found order by id ' . $requestData['orderId']]], status: Response::HTTP_NOT_FOUND);
        }

        $menuItem = $menuItemRepository->find($requestData['menuItemId']);
        if (!$menuItem) {
            return new JsonResponse(['data' => ['error' => 'Not found menu item by id ' . $requestData['menuItemId']]], status: Response::HTTP_NOT_FOUND);
        }

        $orderItem = new OrderItem();
        $orderItem->setOrder($order);
        $orderItem->setMenuItem($menuItem);
        $orderItem->setQuantity($requestData['quantity']);
        $orderItem->setUnitPrice((string) ($requestData['unitPrice'] ?? $menuItem->getPrice()));

        $this->entityManager->persist($orderItem);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $orderItem->toArray()], status: Response::HTTP_CREATED);
    }

    #[Route('/order-items/{id}', name: 'update_order_item', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function updateOrderItem(int $id, Request $request, OrderItemRepository $orderItemRepository): JsonResponse
    {
        $orderItem = $orderItemRepository->find($id);

        if (!$orderItem) {
            return new JsonResponse(['data' => ['error' => 'Not found order item by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        $orderItem->setQuantity($requestData['quantity'] ?? $orderItem->getQuantity());
        $orderItem->setUnitPrice(isset($requestData['unitPrice']) ? (string) $requestData['unitPrice'] : $orderItem->getUnitPrice());

        $this->entityManager->flush();

        return new JsonResponse(['data' => $orderItem->toArray()], status: Response::HTTP_OK);
    }

    #[Route('/order-items/{id}', name: 'delete_order_item', methods: [Request::METHOD_DELETE])]
    public function deleteOrderItem(int $id, OrderItemRepository $orderItemRepository): JsonResponse
    {
        $orderItem = $orderItemRepository->find($id);

        if (!$orderItem) {
            return new JsonResponse(['data' => ['error' => 'Not found order item by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($orderItem);
        $this->entityManager->flush();

        return new JsonResponse(['data' => ['message' => 'Order item ' . $id . ' deleted']], status: Response::HTTP_OK);
    }
}
