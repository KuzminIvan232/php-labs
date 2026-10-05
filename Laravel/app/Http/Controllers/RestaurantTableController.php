<?php

namespace App\Http\Controllers;

use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RestaurantTableController extends Controller
{
    public function getTables(): mixed
    {
        return response()->json(['data' => RestaurantTable::all()], Response::HTTP_OK);
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
