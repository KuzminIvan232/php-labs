<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersAndPaginates;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderItemController extends Controller
{
    use FiltersAndPaginates;

    /**
     * Lab 4: filtering by every field + page/itemsPerPage pagination.
     *
     * Supported query params: id, orderId, menuItemId (exact),
     * quantity_min / quantity_max, unitPrice_min / unitPrice_max.
     */
    public function getOrderItems(Request $request): mixed
    {
        $query = OrderItem::query();

        $this->applyFilters($query, $request, [
            'id' => ['type' => 'exact'],
            'orderId' => ['type' => 'exact', 'column' => 'order_id'],
            'menuItemId' => ['type' => 'exact', 'column' => 'menu_item_id'],
            'quantity' => ['type' => 'range'],
            'unitPrice' => ['type' => 'range', 'column' => 'unit_price'],
        ]);

        $query->orderBy('id');

        $result = $this->paginateQuery($query, $request);

        return response()->json(['data' => $result['items'], 'meta' => $result['meta']], Response::HTTP_OK);
    }

    public function getOrderItemItem(string $id): mixed
    {
        $orderItem = OrderItem::find($id);

        if (!$orderItem) {
            return response()->json(['data' => ['error' => 'Not found order item by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $orderItem], Response::HTTP_OK);
    }

    public function createOrderItem(Request $request): mixed
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $order = Order::find($requestData['orderId']);
        if (!$order) {
            return response()->json(['data' => ['error' => 'Not found order by id ' . $requestData['orderId']]], Response::HTTP_NOT_FOUND);
        }

        $menuItem = MenuItem::find($requestData['menuItemId']);
        if (!$menuItem) {
            return response()->json(['data' => ['error' => 'Not found menu item by id ' . $requestData['menuItemId']]], Response::HTTP_NOT_FOUND);
        }

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => $requestData['quantity'],
            'unit_price' => $requestData['unitPrice'] ?? $menuItem->price,
        ]);

        return response()->json(['data' => $orderItem], Response::HTTP_CREATED);
    }

    public function updateOrderItem(string $id, Request $request): mixed
    {
        $orderItem = OrderItem::find($id);

        if (!$orderItem) {
            return response()->json(['data' => ['error' => 'Not found order item by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        $orderItem->quantity = $requestData['quantity'] ?? $orderItem->quantity;
        $orderItem->unit_price = $requestData['unitPrice'] ?? $orderItem->unit_price;
        $orderItem->save();

        return response()->json(['data' => $orderItem], Response::HTTP_OK);
    }

    public function deleteOrderItem(string $id): mixed
    {
        $orderItem = OrderItem::find($id);

        if (!$orderItem) {
            return response()->json(['data' => ['error' => 'Not found order item by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $orderItem->delete();

        return response()->json(['data' => ['message' => 'Order item ' . $id . ' deleted']], Response::HTTP_OK);
    }
}
