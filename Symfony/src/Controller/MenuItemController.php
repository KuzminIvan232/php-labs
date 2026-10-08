<?php

namespace App\Controller;

use App\Entity\MenuItem;
use App\Repository\MenuItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MenuItemController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/menu-items', name: 'get_menu_items', methods: [Request::METHOD_GET])]
    public function getMenuItems(Request $request, MenuItemRepository $menuItemRepository): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $itemsPerPage = max(1, (int) $request->query->get('itemsPerPage', 10));

        $result = $menuItemRepository->search($request->query->all(), $page, $itemsPerPage);

        return new JsonResponse([
            'data' => array_map(static fn (MenuItem $menuItem) => $menuItem->toArray(), $result['items']),
            'meta' => $result['meta'],
        ], status: Response::HTTP_OK);
    }

    #[Route('/menu-items/{id}', name: 'get_menu_item_item', methods: [Request::METHOD_GET])]
    public function getMenuItemItem(int $id, MenuItemRepository $menuItemRepository): JsonResponse
    {
        $menuItem = $menuItemRepository->find($id);

        if (!$menuItem) {
            return new JsonResponse(['data' => ['error' => 'Not found menu item by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $menuItem->toArray()], status: Response::HTTP_OK);
    }

    #[Route('/menu-items', name: 'post_menu_items', methods: [Request::METHOD_POST])]
    public function createMenuItem(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $menuItem = new MenuItem();
        $menuItem->setName($requestData['name']);
        $menuItem->setDescription($requestData['description'] ?? null);
        $menuItem->setPrice((string) $requestData['price']);
        $menuItem->setCategory($requestData['category'] ?? null);

        $this->entityManager->persist($menuItem);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $menuItem->toArray()], status: Response::HTTP_CREATED);
    }

    #[Route('/menu-items/{id}', name: 'update_menu_item', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function updateMenuItem(int $id, Request $request, MenuItemRepository $menuItemRepository): JsonResponse
    {
        $menuItem = $menuItemRepository->find($id);

        if (!$menuItem) {
            return new JsonResponse(['data' => ['error' => 'Not found menu item by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        $menuItem->setName($requestData['name'] ?? $menuItem->getName());
        $menuItem->setDescription(array_key_exists('description', $requestData) ? $requestData['description'] : $menuItem->getDescription());
        $menuItem->setPrice(isset($requestData['price']) ? (string) $requestData['price'] : $menuItem->getPrice());
        $menuItem->setCategory(array_key_exists('category', $requestData) ? $requestData['category'] : $menuItem->getCategory());

        $this->entityManager->flush();

        return new JsonResponse(['data' => $menuItem->toArray()], status: Response::HTTP_OK);
    }

    #[Route('/menu-items/{id}', name: 'delete_menu_item', methods: [Request::METHOD_DELETE])]
    public function deleteMenuItem(int $id, MenuItemRepository $menuItemRepository): JsonResponse
    {
        $menuItem = $menuItemRepository->find($id);

        if (!$menuItem) {
            return new JsonResponse(['data' => ['error' => 'Not found menu item by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($menuItem);
        $this->entityManager->flush();

        return new JsonResponse(['data' => ['message' => 'Menu item ' . $id . ' deleted']], status: Response::HTTP_OK);
    }
}
