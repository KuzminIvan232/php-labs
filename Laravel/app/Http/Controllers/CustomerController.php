<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersAndPaginates;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    use FiltersAndPaginates;

    /**
     * Lab 4: filtering by every field + page/itemsPerPage pagination.
     *
     * Supported query params: id (exact), name, phone, email (partial match),
     * createdAt_min / createdAt_max (ISO-8601).
     */
    public function getCustomers(Request $request): mixed
    {
        $query = Customer::query();

        $this->applyFilters($query, $request, [
            'id' => ['type' => 'exact'],
            'name' => ['type' => 'like'],
            'phone' => ['type' => 'like'],
            'email' => ['type' => 'like'],
            'createdAt' => ['type' => 'range', 'column' => 'created_at'],
        ]);

        $query->orderBy('id');

        $result = $this->paginateQuery($query, $request);

        return response()->json(['data' => $result['items'], 'meta' => $result['meta']], Response::HTTP_OK);
    }

    public function getCustomerItem(string $id): mixed
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['data' => ['error' => 'Not found customer by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $customer], Response::HTTP_OK);
    }

    public function createCustomer(Request $request): mixed
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $customer = Customer::create([
            'name' => $requestData['name'],
            'phone' => $requestData['phone'],
            'email' => $requestData['email'] ?? null,
        ]);

        return response()->json(['data' => $customer], Response::HTTP_CREATED);
    }

    public function updateCustomer(string $id, Request $request): mixed
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['data' => ['error' => 'Not found customer by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);

        $customer->fill([
            'name' => $requestData['name'] ?? $customer->name,
            'phone' => $requestData['phone'] ?? $customer->phone,
            'email' => array_key_exists('email', $requestData) ? $requestData['email'] : $customer->email,
        ])->save();

        return response()->json(['data' => $customer], Response::HTTP_OK);
    }

    public function deleteCustomer(string $id): mixed
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['data' => ['error' => 'Not found customer by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $customer->delete();

        return response()->json(['data' => ['message' => 'Customer ' . $id . ' deleted']], Response::HTTP_OK);
    }
}
