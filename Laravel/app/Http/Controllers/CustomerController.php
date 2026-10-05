<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    public function getCustomers(): mixed
    {
        return response()->json(['data' => Customer::all()], Response::HTTP_OK);
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
