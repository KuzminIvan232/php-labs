<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersAndPaginates;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MenuItemController extends Controller
{
    use FiltersAndPaginates;

    /**
     * Lab 4: filtering by every field + page/itemsPerPage pagination.
     *
     * Supported query params: id (exact), name, description, category (partial match),
     * price_min / price_max.
     */
    public function getMenuItems(Request $request): mixed
    {
        $query = MenuItem::query();

        $this->applyFilters($query, $request, [
            'id' => ['type' => 'exact'],
            'name' => ['type' => 'like'],
            'description' => ['type' => 'like'],
            'price' => ['type' => 'range'],
            'category' => ['type' => 'like'],
        ]);

        $query->orderBy('id');

        $result = $this->paginateQuery($query, $request);

        return response()->json(['data' => $result['items'], 'meta' => $result['meta']], Response::HTTP_OK);
    }

    public function getMenuItemItem(string $id): mixed
    {
        $menuItem = MenuItem::find($id);

        if (!$menuItem) {
            return response()->json(['data' => ['error' => 'Not found menu item by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $menuItem], Response::HTTP_OK);
    }

    public function createMenuItem(Request $request): mixed
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $menuItem = MenuItem::create([
            'name' => $requestData['name'],
            'description' => $requestData['description'] ?? null,
            'price' => $requestData['price'],
            'category' => $requestData['category'] ?? null,
        ]);

        return response()->json(['data' => $menuItem], Response::HTTP_CREATED);
    }

    public function updateMenuItem(string $id, Request $request): mixed
    {
        $menuItem = MenuItem::find($id);

        if (!$menuItem) {
            return response()->json(['data' => ['error' => 'Not found menu item by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        $menuItem->fill([
            'name' => $requestData['name'] ?? $menuItem->name,
            'description' => array_key_exists('description', $requestData) ? $requestData['description'] : $menuItem->description,
            'price' => $requestData['price'] ?? $menuItem->price,
            'category' => array_key_exists('category', $requestData) ? $requestData['category'] : $menuItem->category,
        ])->save();

        return response()->json(['data' => $menuItem], Response::HTTP_OK);
    }

    public function deleteMenuItem(string $id): mixed
    {
        $menuItem = MenuItem::find($id);

        if (!$menuItem) {
            return response()->json(['data' => ['error' => 'Not found menu item by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $menuItem->delete();

        return response()->json(['data' => ['message' => 'Menu item ' . $id . ' deleted']], Response::HTTP_OK);
    }
}
