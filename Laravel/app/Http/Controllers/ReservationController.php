<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersAndPaginates;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReservationController extends Controller
{
    use FiltersAndPaginates;

    /**
     * Lab 4: filtering by every field + page/itemsPerPage pagination.
     *
     * Supported query params: id, customerId, restaurantTableId, status (exact),
     * reservedFor_min / reservedFor_max, guestsCount_min / guestsCount_max,
     * createdAt_min / createdAt_max.
     */
    public function getReservations(Request $request): mixed
    {
        $query = Reservation::query();

        $this->applyFilters($query, $request, [
            'id' => ['type' => 'exact'],
            'customerId' => ['type' => 'exact', 'column' => 'customer_id'],
            'restaurantTableId' => ['type' => 'exact', 'column' => 'restaurant_table_id'],
            'reservedFor' => ['type' => 'range', 'column' => 'reserved_for'],
            'guestsCount' => ['type' => 'range', 'column' => 'guests_count'],
            'status' => ['type' => 'exact'],
            'createdAt' => ['type' => 'range', 'column' => 'created_at'],
        ]);

        $query->orderBy('id');

        $result = $this->paginateQuery($query, $request);

        return response()->json(['data' => $result['items'], 'meta' => $result['meta']], Response::HTTP_OK);
    }

    public function getReservationItem(string $id): mixed
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['data' => ['error' => 'Not found reservation by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $reservation], Response::HTTP_OK);
    }

    public function createReservation(Request $request): mixed
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $customer = Customer::find($requestData['customerId']);
        if (!$customer) {
            return response()->json(['data' => ['error' => 'Not found customer by id ' . $requestData['customerId']]], Response::HTTP_NOT_FOUND);
        }

        $table = RestaurantTable::find($requestData['restaurantTableId']);
        if (!$table) {
            return response()->json(['data' => ['error' => 'Not found table by id ' . $requestData['restaurantTableId']]], Response::HTTP_NOT_FOUND);
        }

        $reservation = Reservation::create([
            'customer_id' => $customer->id,
            'restaurant_table_id' => $table->id,
            'reserved_for' => $requestData['reservedFor'],
            'guests_count' => $requestData['guestsCount'],
            'status' => $requestData['status'] ?? 'pending',
        ]);

        return response()->json(['data' => $reservation], Response::HTTP_CREATED);
    }

    public function updateReservation(string $id, Request $request): mixed
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['data' => ['error' => 'Not found reservation by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        if (isset($requestData['customerId'])) {
            $customer = Customer::find($requestData['customerId']);
            if (!$customer) {
                return response()->json(['data' => ['error' => 'Not found customer by id ' . $requestData['customerId']]], Response::HTTP_NOT_FOUND);
            }
            $reservation->customer_id = $customer->id;
        }

        if (isset($requestData['restaurantTableId'])) {
            $table = RestaurantTable::find($requestData['restaurantTableId']);
            if (!$table) {
                return response()->json(['data' => ['error' => 'Not found table by id ' . $requestData['restaurantTableId']]], Response::HTTP_NOT_FOUND);
            }
            $reservation->restaurant_table_id = $table->id;
        }

        $reservation->reserved_for = $requestData['reservedFor'] ?? $reservation->reserved_for;
        $reservation->guests_count = $requestData['guestsCount'] ?? $reservation->guests_count;
        $reservation->status = $requestData['status'] ?? $reservation->status;
        $reservation->save();

        return response()->json(['data' => $reservation], Response::HTTP_OK);
    }

    public function deleteReservation(string $id): mixed
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['data' => ['error' => 'Not found reservation by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $reservation->delete();

        return response()->json(['data' => ['message' => 'Reservation ' . $id . ' deleted']], Response::HTTP_OK);
    }
}
