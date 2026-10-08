<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersAndPaginates;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use FiltersAndPaginates;

    /**
     * Lab 4: filtering by every field + page/itemsPerPage pagination.
     *
     * Supported query params: id, customerId, restaurantTableId, status (exact),
     * orderedAt_min / orderedAt_max.
     */
    public function getOrders(Request $request): mixed
    {
        $query = Order::with('orderItems');

        $this->applyFilters($query, $request, [
            'id' => ['type' => 'exact'],
            'customerId' => ['type' => 'exact', 'column' => 'customer_id'],
            'restaurantTableId' => ['type' => 'exact', 'column' => 'restaurant_table_id'],
            'status' => ['type' => 'exact'],
            'orderedAt' => ['type' => 'range', 'column' => 'ordered_at'],
        ]);

        $query->orderBy('id');

        $result = $this->paginateQuery($query, $request);

        return response()->json(['data' => $result['items'], 'meta' => $result['meta']], Response::HTTP_OK);
    }

    public function getOrderItem(string $id): mixed
    {
        $order = Order::with('orderItems')->find($id);

        if (!$order) {
            return response()->json(['data' => ['error' => 'Not found order by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $order], Response::HTTP_OK);
    }

    /**
     * Creates an order together with its order items in one request, e.g.:
     * {
     *   "customerId": 1,
     *   "restaurantTableId": 2,
     *   "items": [{"menuItemId": 3, "quantity": 2}, {"menuItemId": 5, "quantity": 1}]
     * }
     */
    public function createOrder(Request $request): mixed
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $customer = Customer::find($requestData['customerId']);
        if (!$customer) {
            return response()->json(['data' => ['error' => 'Not found customer by id ' . $requestData['customerId']]], Response::HTTP_NOT_FOUND);
        }

        $table = null;
        if (!empty($requestData['restaurantTableId'])) {
            $table = RestaurantTable::find($requestData['restaurantTableId']);
            if (!$table) {
                return response()->json(['data' => ['error' => 'Not found table by id ' . $requestData['restaurantTableId']]], Response::HTTP_NOT_FOUND);
            }
        }

        $items = $requestData['items'] ?? [];
        foreach ($items as $itemData) {
            if (!MenuItem::find($itemData['menuItemId'])) {
                return response()->json(['data' => ['error' => 'Not found menu item by id ' . $itemData['menuItemId']]], Response::HTTP_NOT_FOUND);
            }
        }

        $order = DB::transaction(function () use ($customer, $table, $requestData, $items) {
            $order = Order::create([
                'customer_id' => $customer->id,
                'restaurant_table_id' => $table?->id,
                'status' => $requestData['status'] ?? 'new',
                'ordered_at' => now(),
            ]);

            foreach ($items as $itemData) {
                $menuItem = MenuItem::find($itemData['menuItemId']);
                $order->orderItems()->create([
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $menuItem->price,
                ]);
            }

            return $order;
        });

        return response()->json(['data' => $order->load('orderItems')], Response::HTTP_CREATED);
    }

    public function updateOrder(string $id, Request $request): mixed
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['data' => ['error' => 'Not found order by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        if (array_key_exists('restaurantTableId', $requestData)) {
            $table = $requestData['restaurantTableId'] ? RestaurantTable::find($requestData['restaurantTableId']) : null;
            if ($requestData['restaurantTableId'] && !$table) {
                return response()->json(['data' => ['error' => 'Not found table by id ' . $requestData['restaurantTableId']]], Response::HTTP_NOT_FOUND);
            }
            $order->restaurant_table_id = $table?->id;
        }

        $order->status = $requestData['status'] ?? $order->status;
        $order->save();

        return response()->json(['data' => $order->load('orderItems')], Response::HTTP_OK);
    }

    public function deleteOrder(string $id): mixed
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['data' => ['error' => 'Not found order by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $order->delete();

        return response()->json(['data' => ['message' => 'Order ' . $id . ' deleted']], Response::HTTP_OK);
    }
}
