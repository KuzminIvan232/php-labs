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

class RestaurantTableController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/tables', name: 'get_tables', methods: [Request::METHOD_GET])]
    public function getTables(RestaurantTableRepository $tableRepository): JsonResponse
    {
        $tables = array_map(
            static fn (RestaurantTable $table) => $table->toArray(),
            $tableRepository->findAll()
        );

        return new JsonResponse(['data' => $tables], status: Response::HTTP_OK);
    }

    #[Route('/tables/{id}', name: 'get_table_item', methods: [Request::METHOD_GET])]
    public function getTableItem(int $id, RestaurantTableRepository $tableRepository): JsonResponse
    {
        $table = $tableRepository->find($id);

        if (!$table) {
            return new JsonResponse(['data' => ['error' => 'Not found table by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $table->toArray()], status: Response::HTTP_OK);
    }

    #[Route('/tables', name: 'post_tables', methods: [Request::METHOD_POST])]
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
