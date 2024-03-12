<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders=Order::query();
        $scoped=[
          'status',
           'order_key',
           'customer',
        ];
        foreach($scoped as $scope){
            if($request->query($scope)){
                $scopeMethod=Str::camel($scope);
                $orders->$scopeMethod($request->query($scope));
            }
        }
        if(in_array($request->query('sortBy'),(new Order())->getFillable())&&in_array($request->query('sortType'),['asc','desc'])){
            $orders->orderBy($request->query('sortBy'),$request->query('sortType'));
        }
        return OrderResource::collection($orders->paginate($request->query('per_page')));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function sync()
    {
        //
    }


}
