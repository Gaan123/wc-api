<?php

namespace App\Services;

use App\Models\LineItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrdersFetch
{
    public function handle()
    {
        $baseUrl = env('WOOCOMMERCE_STORE_URL');
        $consumerKey = env('WOOCOMMERCE_CONSUMER_KEY');
        $consumerSecret = env('WOOCOMMERCE_CONSUMER_SECRET');
        $page = 1;
        $perPage = 12;

        DB::beginTransaction();

        try {


            do {
                try {
                    $response = Http::withBasicAuth($consumerKey, $consumerSecret)
                        ->get("{$baseUrl}/wp-json/wc/v3/orders", [
                            'page' => $page,
                            'per_page' => $perPage,
                            'after' => now()->subDays(30)->toIso8601String(),
                        ]);

                    if ($response->successful()) {
                        $orders = $response->json();
                        foreach ($orders as $key=>$order){
                            try {
                                $existingOrder = Order::where('order_key', $order['order_key'])->first();

                                if ($existingOrder) {
                                    if ($existingOrder->updated_at->toDateTimeString() !== $order['date_modified']) {
                                        $savedOrder = $existingOrder->update([
                                            'number' => $order['number'],
                                            'status' => $order['status'],
                                            'total' => $order['total'],
                                            'customer_note' => $order['customer_note'],
                                            'billing' => $order['billing'],
                                            'shipping' => $order['shipping'],
                                            'customer_id' => $order['customer_id'],
                                            'updated_at' => $order['date_modified']
                                        ]);
                                    }else{
                                        continue;
                                    }
                                } else {
                                    $savedOrder = Order::create([
                                        'order_key' => $order['order_key'],
                                        'number' => $order['number'],
                                        'status' => $order['status'],
                                        'created_at' => $order['date_created'],
                                        'total' => $order['total'],
                                        'customer_note' => $order['customer_note'],
                                        'customer_id' => $order['customer_id'],
                                        'billing' => $order['billing'],
                                        'shipping' => $order['shipping']
                                    ]);
                                }
                                array_map(function($item) use($savedOrder){
                                    $lineItem=array_intersect_key($item, array_flip((new LineItem())->getFillable()));
                                    $lineItem['meta_data']=json_encode($lineItem['meta_data']);
                                    $lineItem['taxes']=json_encode($lineItem['taxes']);
                                    $lineItem['total_tax']=(float)$lineItem['taxes'];

                                    if ($lineItemD=LineItem::find($item['id'])){
                                        $lineItemD->update($lineItem);
                                    }else{
                                        $lineItemD->create([...$lineItem,'order_id'=>$savedOrder->id]);
                                    }

                                    return $lineItem;
                                }, $order['line_items']);

                            }catch (\Exception $exception){
                                Log::warning($exception->getMessage());
                                dd($exception->getMessage());
                            }

                        }
                        $page++;
                    } else {
                        Log::error($response->status() . "- Failed to fetch orders");
                        throw new \Exception($response->status() . "- Failed to fetch orders");
                    }
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                    throw new \Exception($e->getMessage());
                }
            } while ($orders && count($orders) === $perPage);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
