<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $baseUrl = env('WOOCOMMERCE_STORE_URL');
        $consumerKey = env('WOOCOMMERCE_CONSUMER_KEY');
        $consumerSecret = env('WOOCOMMERCE_CONSUMER_SECRET');
        $page = 1;
        $perPage = 12;

        try {
            do {
                $response = Http::withBasicAuth($consumerKey, $consumerSecret)
                    ->get("{$baseUrl}/wp-json/wc/v3/orders", [
                        'page' => $page,
                        'per_page' => $perPage,
                        'after' => now()->subDays(30)->toIso8601String(),
                    ]);

                if ($response->successful()) {
                    $orders = $response->json();
                    $page++;
                } else {
                    return response()->json(['error' => 'Failed to fetch orders'], $response->status());
                }

            } while (count($orders) === $perPage);

            return response()->json(['success' => 'Order fetched successfully', 'data' => $orders]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $orders)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $orders)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $orders)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $orders)
    {
        //
    }
}
