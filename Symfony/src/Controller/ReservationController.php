<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\CustomerRepository;
use App\Repository\ReservationRepository;
use App\Repository\RestaurantTableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReservationController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    // Lab 5: listing/viewing all reservations is staff-only (Manager/Admin) —
    // a Client only places reservations, it does not browse everyone else's.
    #[Route('/reservations', name: 'get_reservations', methods: [Request::METHOD_GET])]
    #[IsGranted('ROLE_MANAGER')]
    public function getReservations(Request $request, ReservationRepository $reservationRepository): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $itemsPerPage = max(1, (int) $request->query->get('itemsPerPage', 10));

        $result = $reservationRepository->search($request->query->all(), $page, $itemsPerPage);

        return new JsonResponse([
            'data' => array_map(static fn (Reservation $reservation) => $reservation->toArray(), $result['items']),
            'meta' => $result['meta'],
        ], status: Response::HTTP_OK);
    }

    #[Route('/reservations/{id}', name: 'get_reservation_item', methods: [Request::METHOD_GET])]
    #[IsGranted('ROLE_MANAGER')]
    public function getReservationItem(int $id, ReservationRepository $reservationRepository): JsonResponse
    {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            return new JsonResponse(['data' => ['error' => 'Not found reservation by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $reservation->toArray()], status: Response::HTTP_OK);
    }

    // Lab 5: any authenticated role (Client and up) can book a reservation.
    #[Route('/reservations', name: 'post_reservations', methods: [Request::METHOD_POST])]
    #[IsGranted('ROLE_CLIENT')]
    public function createReservation(
        Request $request,
        CustomerRepository $customerRepository,
        RestaurantTableRepository $tableRepository
    ): JsonResponse {
        $requestData = json_decode($request->getContent(), associative: true);

        $customer = $customerRepository->find($requestData['customerId']);
        if (!$customer) {
            return new JsonResponse(['data' => ['error' => 'Not found customer by id ' . $requestData['customerId']]], status: Response::HTTP_NOT_FOUND);
        }

        $table = $tableRepository->find($requestData['restaurantTableId']);
        if (!$table) {
            return new JsonResponse(['data' => ['error' => 'Not found table by id ' . $requestData['restaurantTableId']]], status: Response::HTTP_NOT_FOUND);
        }

        $reservation = new Reservation();
        $reservation->setCustomer($customer);
        $reservation->setRestaurantTable($table);
        $reservation->setReservedFor(new \DateTimeImmutable($requestData['reservedFor']));
        $reservation->setGuestsCount($requestData['guestsCount']);
        $reservation->setStatus($requestData['status'] ?? 'pending');

        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $reservation->toArray()], status: Response::HTTP_CREATED);
    }

    // Lab 5: changing/cancelling a reservation on someone's behalf is staff-only.
    #[Route('/reservations/{id}', name: 'update_reservation', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    #[IsGranted('ROLE_MANAGER')]
    public function updateReservation(
        int $id,
        Request $request,
        ReservationRepository $reservationRepository,
        CustomerRepository $customerRepository,
        RestaurantTableRepository $tableRepository
    ): JsonResponse {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            return new JsonResponse(['data' => ['error' => 'Not found reservation by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        if (isset($requestData['customerId'])) {
            $customer = $customerRepository->find($requestData['customerId']);
            if (!$customer) {
                return new JsonResponse(['data' => ['error' => 'Not found customer by id ' . $requestData['customerId']]], status: Response::HTTP_NOT_FOUND);
            }
            $reservation->setCustomer($customer);
        }

        if (isset($requestData['restaurantTableId'])) {
            $table = $tableRepository->find($requestData['restaurantTableId']);
            if (!$table) {
                return new JsonResponse(['data' => ['error' => 'Not found table by id ' . $requestData['restaurantTableId']]], status: Response::HTTP_NOT_FOUND);
            }
            $reservation->setRestaurantTable($table);
        }

        if (isset($requestData['reservedFor'])) {
            $reservation->setReservedFor(new \DateTimeImmutable($requestData['reservedFor']));
        }

        $reservation->setGuestsCount($requestData['guestsCount'] ?? $reservation->getGuestsCount());
        $reservation->setStatus($requestData['status'] ?? $reservation->getStatus());

        $this->entityManager->flush();

        return new JsonResponse(['data' => $reservation->toArray()], status: Response::HTTP_OK);
    }

    #[Route('/reservations/{id}', name: 'delete_reservation', methods: [Request::METHOD_DELETE])]
    #[IsGranted('ROLE_MANAGER')]
    public function deleteReservation(int $id, ReservationRepository $reservationRepository): JsonResponse
    {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            return new JsonResponse(['data' => ['error' => 'Not found reservation by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($reservation);
        $this->entityManager->flush();

        return new JsonResponse(['data' => ['message' => 'Reservation ' . $id . ' deleted']], status: Response::HTTP_OK);
    }
}
