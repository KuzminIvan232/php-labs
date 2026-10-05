<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RestaurantTableController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', [TestController::class, 'index']);

Route::get('/products', [ProductController::class, 'getProducts']);
Route::get('/products/{id}', [ProductController::class, 'getProductItem']);
Route::post('/products', [ProductController::class, 'createProduct']);
Route::put('/products/{id}', [ProductController::class, 'updateProduct']);
Route::patch('/products/{id}', [ProductController::class, 'updateProduct']);
Route::delete('/products/{id}', [ProductController::class, 'deleteProduct']);

Route::get('/customers', [CustomerController::class, 'getCustomers']);
Route::get('/customers/{id}', [CustomerController::class, 'getCustomerItem']);
Route::post('/customers', [CustomerController::class, 'createCustomer']);
Route::put('/customers/{id}', [CustomerController::class, 'updateCustomer']);
Route::patch('/customers/{id}', [CustomerController::class, 'updateCustomer']);
Route::delete('/customers/{id}', [CustomerController::class, 'deleteCustomer']);

Route::get('/tables', [RestaurantTableController::class, 'getTables']);
Route::get('/tables/{id}', [RestaurantTableController::class, 'getTableItem']);
Route::post('/tables', [RestaurantTableController::class, 'createTable']);
Route::put('/tables/{id}', [RestaurantTableController::class, 'updateTable']);
Route::patch('/tables/{id}', [RestaurantTableController::class, 'updateTable']);
Route::delete('/tables/{id}', [RestaurantTableController::class, 'deleteTable']);

Route::get('/menu-items', [MenuItemController::class, 'getMenuItems']);
Route::get('/menu-items/{id}', [MenuItemController::class, 'getMenuItemItem']);
Route::post('/menu-items', [MenuItemController::class, 'createMenuItem']);
Route::put('/menu-items/{id}', [MenuItemController::class, 'updateMenuItem']);
Route::patch('/menu-items/{id}', [MenuItemController::class, 'updateMenuItem']);
Route::delete('/menu-items/{id}', [MenuItemController::class, 'deleteMenuItem']);

Route::get('/reservations', [ReservationController::class, 'getReservations']);
Route::get('/reservations/{id}', [ReservationController::class, 'getReservationItem']);
Route::post('/reservations', [ReservationController::class, 'createReservation']);
Route::put('/reservations/{id}', [ReservationController::class, 'updateReservation']);
Route::patch('/reservations/{id}', [ReservationController::class, 'updateReservation']);
Route::delete('/reservations/{id}', [ReservationController::class, 'deleteReservation']);

Route::get('/orders', [OrderController::class, 'getOrders']);
Route::get('/orders/{id}', [OrderController::class, 'getOrderItem']);
Route::post('/orders', [OrderController::class, 'createOrder']);
Route::put('/orders/{id}', [OrderController::class, 'updateOrder']);
Route::patch('/orders/{id}', [OrderController::class, 'updateOrder']);
Route::delete('/orders/{id}', [OrderController::class, 'deleteOrder']);

Route::get('/order-items', [OrderItemController::class, 'getOrderItems']);
Route::get('/order-items/{id}', [OrderItemController::class, 'getOrderItemItem']);
Route::post('/order-items', [OrderItemController::class, 'createOrderItem']);
Route::put('/order-items/{id}', [OrderItemController::class, 'updateOrderItem']);
Route::patch('/order-items/{id}', [OrderItemController::class, 'updateOrderItem']);
Route::delete('/order-items/{id}', [OrderItemController::class, 'deleteOrderItem']);
