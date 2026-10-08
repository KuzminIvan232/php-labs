<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CustomerController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/customers', name: 'get_customers', methods: [Request::METHOD_GET])]
    public function getCustomers(Request $request, CustomerRepository $customerRepository): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $itemsPerPage = max(1, (int) $request->query->get('itemsPerPage', 10));

        $result = $customerRepository->search($request->query->all(), $page, $itemsPerPage);

        return new JsonResponse([
            'data' => array_map(static fn (Customer $customer) => $customer->toArray(), $result['items']),
            'meta' => $result['meta'],
        ], status: Response::HTTP_OK);
    }

    #[Route('/customers/{id}', name: 'get_customer_item', methods: [Request::METHOD_GET])]
    public function getCustomerItem(int $id, CustomerRepository $customerRepository): JsonResponse
    {
        $customer = $customerRepository->find($id);

        if (!$customer) {
            return new JsonResponse(['data' => ['error' => 'Not found customer by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $customer->toArray()], status: Response::HTTP_OK);
    }

    #[Route('/customers', name: 'post_customers', methods: [Request::METHOD_POST])]
    public function createCustomer(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $customer = new Customer();
        $customer->setName($requestData['name']);
        $customer->setPhone($requestData['phone']);
        $customer->setEmail($requestData['email'] ?? null);

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $customer->toArray()], status: Response::HTTP_CREATED);
    }

    #[Route('/customers/{id}', name: 'update_customer', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function updateCustomer(int $id, Request $request, CustomerRepository $customerRepository): JsonResponse
    {
        $customer = $customerRepository->find($id);

        if (!$customer) {
            return new JsonResponse(['data' => ['error' => 'Not found customer by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        $customer->setName($requestData['name'] ?? $customer->getName());
        $customer->setPhone($requestData['phone'] ?? $customer->getPhone());
        $customer->setEmail(array_key_exists('email', $requestData) ? $requestData['email'] : $customer->getEmail());

        $this->entityManager->flush();

        return new JsonResponse(['data' => $customer->toArray()], status: Response::HTTP_OK);
    }

    #[Route('/customers/{id}', name: 'delete_customer', methods: [Request::METHOD_DELETE])]
    public function deleteCustomer(int $id, CustomerRepository $customerRepository): JsonResponse
    {
        $customer = $customerRepository->find($id);

        if (!$customer) {
            return new JsonResponse(['data' => ['error' => 'Not found customer by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($customer);
        $this->entityManager->flush();

        return new JsonResponse(['data' => ['message' => 'Customer ' . $id . ' deleted']], status: Response::HTTP_OK);
    }
}
