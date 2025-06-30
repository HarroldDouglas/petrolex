<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetOrderListController extends Controller
{
    /**
     * Display a listing of the orders.
     *
     * Route: GET /orders
     * Name: orders.list
     */
    public function __invoke(Request $request)
    {
        //
        return view('orders.index');
    }
}
