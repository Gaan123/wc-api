<?php

namespace App\Console\Commands;

use App\Models\LineItem;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchWoocomerceOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-woocomerce-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
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
                                $savedOrder=Order::create([
                                    'number'=>$order['number'], 'order_key'=>$order['order_key'],
                                    'status'=>$order['status'],
                                    'created_at'=>$order['date_created'],
                                    'updated_at'=>$order['date_modified'],
                                    'total'=>$order['total'],
                                    'customer_id'=>$order['customer_id'],
                                    'customer_note'=>$order['customer_note'],
                                    'billing'=>$order['billing'], 'shipping'=>$order['shipping']
                                ]);
//                                $pluckedValues = collect($order['line_items'])
//                                    ->map(function ($item) {
//                                        return array_intersect_key($item, array_flip((new LineItem())->getFillable())) ;
//                                    })
//                                    ->toArray();
                                $pluckedValues = array_map(function($item) {

                                    $lineItem=array_intersect_key($item, array_flip((new LineItem())->getFillable()));
                                    $lineItem['meta_data']=json_encode($lineItem['meta_data']);
                                    $lineItem['taxes']=json_encode($lineItem['taxes']);
                                    $lineItem['total_tax']=(float)$lineItem['taxes'];
                                    return $lineItem;
                                }, $order['line_items']);
//                                $pluckedValues = collect($order['line_items'])->pluck((new LineItem())->getFillable());
//                                dd($pluckedValues,collect($order['line_items'])->pluck((new LineItem())->getFillable()),(new LineItem())->getFillable(),$order['line_items']);
                                $savedOrder->lineItems()->createMany($pluckedValues);
                            }catch (\Exception $exception){
                                dd($exception->getMessage(),$key);
                            }

                        }
                        $page++;
                    } else {
                        Log::error($response->status() . "- Failed to fetch orders");
                        throw new \Exception('Failed to fetch orders');
                    }
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                    throw $e;
                }
            } while ($orders && count($orders) === $perPage);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
        }

    }
}
