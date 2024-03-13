<?php

namespace App\Console\Commands;

use App\Models\LineItem;
use App\Models\Order;
use App\Services\OrdersFetch;
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
     * @throws \Exception
     */
    public function handle(OrdersFetch $fetch)
    {
        try {
            $fetch->handle();
        }catch (\Exception $exception){
            Log::driver('error_notification')->error($exception->getMessage());
        }

    }
}
