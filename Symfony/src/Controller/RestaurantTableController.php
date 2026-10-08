<?php

namespace App\Controller;

use App\Entity\RestaurantTable;
use App\Repository\RestaurantTableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RestaurantTableController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    // Lab 5: any authenticated role (Client and up) can browse tables.
    #[Route('/tables', name: 'get_tables', methods: [Request::METHOD_GET])]
    #[IsGranted('ROLE_CLIENT')]
    public function getTables(Request $request, RestaurantTableRepository $tableRepository): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $itemsPerPage = max(1, (int) $request->query->get('itemsPerPage', 10));

        $result = $tableRepository->search($request->query->all(), $page, $itemsPerPage);

        return new JsonResponse([
            'data' => array_map(static fn (RestaurantTable $table) => $table->toArray(), $result['items']),
            'meta' => $result['meta'],
        ], status: Response::HTTP_OK);
    }

    #[Route('/tables/{id}', name: 'get_table_item', methods: [Request::METHOD_GET])]
    #[IsGranted('ROLE_CLIENT')]
    public function getTableItem(int $id, RestaurantTableRepository $tableRepository): JsonResponse
    {
        $table = $tableRepository->find($id);

        if (!$table) {
            return new JsonResponse(['data' => ['error' => 'Not found table by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $table->toArray()], status: Response::HTTP_OK);
    }

    // Lab 5: managing tables (create/update/delete) requires Manager or Admin.
    #[Route('/tables', name: 'post_tables', methods: [Request::METHOD_POST])]
    #[IsGranted('ROLE_MANAGER')]
    public function createTable(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $table = new RestaurantTable();
        $table->setTableNumber($requestData['tableNumber']);
        $table->setSeats($requestData['seats']);

        $this->entityManager->persist($table);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $table->toArray()], status: Response::HTTP_CREATED);
    }

    #[Route('/tables/{id}', name: 'update_table', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    #[IsGranted('ROLE_MANAGER')]
    public function updateTable(int $id, Request $request, RestaurantTableRepository $tableRepository): JsonResponse
    {
        $table = $tableRepository->find($id);

        if (!$table) {
            return new JsonResponse(['data' => ['error' => 'Not found table by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        $table->setTableNumber($requestData['tableNumber'] ?? $table->getTableNumber());
        $table->setSeats($requestData['seats'] ?? $table->getSeats());

        $this->entityManager->flush();

        return new JsonResponse(['data' => $table->toArray()], status: Response::HTTP_OK);
    }

    #[Route('/tables/{id}', name: 'delete_table', methods: [Request::METHOD_DELETE])]
    #[IsGranted('ROLE_MANAGER')]
    public function deleteTable(int $id, RestaurantTableRepository $tableRepository): JsonResponse
    {
        $table = $tableRepository->find($id);

        if (!$table) {
            return new JsonResponse(['data' => ['error' => 'Not found table by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($table);
        $this->entityManager->flush();

        return new JsonResponse(['data' => ['message' => 'Table ' . $id . ' deleted']], status: Response::HTTP_OK);
    }
}
