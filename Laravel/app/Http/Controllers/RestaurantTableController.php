<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersAndPaginates;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RestaurantTableController extends Controller
{
    use FiltersAndPaginates;

    /**
     * Lab 4: filtering by every field + page/itemsPerPage pagination.
     *
     * Supported query params: id, tableNumber (exact), seats_min / seats_max.
     */
    public function getTables(Request $request): mixed
    {
        $query = RestaurantTable::query();

        $this->applyFilters($query, $request, [
            'id' => ['type' => 'exact'],
            'tableNumber' => ['type' => 'exact', 'column' => 'table_number'],
            'seats' => ['type' => 'range'],
        ]);

        $query->orderBy('id');

        $result = $this->paginateQuery($query, $request);

        return response()->json(['data' => $result['items'], 'meta' => $result['meta']], Response::HTTP_OK);
    }

    public function getTableItem(string $id): mixed
    {
        $table = RestaurantTable::find($id);

        if (!$table) {
            return response()->json(['data' => ['error' => 'Not found table by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $table], Response::HTTP_OK);
    }

    public function createTable(Request $request): mixed
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $table = RestaurantTable::create([
            'table_number' => $requestData['tableNumber'],
            'seats' => $requestData['seats'],
        ]);

        return response()->json(['data' => $table], Response::HTTP_CREATED);
    }

    public function updateTable(string $id, Request $request): mixed
    {
        $table = RestaurantTable::find($id);

        if (!$table) {
            return response()->json(['data' => ['error' => 'Not found table by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        $table->fill([
            'table_number' => $requestData['tableNumber'] ?? $table->table_number,
            'seats' => $requestData['seats'] ?? $table->seats,
        ])->save();

        return response()->json(['data' => $table], Response::HTTP_OK);
    }

    public function deleteTable(string $id): mixed
    {
        $table = RestaurantTable::find($id);

        if (!$table) {
            return response()->json(['data' => ['error' => 'Not found table by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $table->delete();

        return response()->json(['data' => ['message' => 'Table ' . $id . ' deleted']], Response::HTTP_OK);
    }
}
