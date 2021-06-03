<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\Cart as CartResource;
use App\Order;
use App\Product;
use Illuminate\Support\Facades\Auth;


class CartController extends BaseController
{
    public function index(){
        // this function for admin to get all order
        $cart_info = Order::where('check',1)->get();
        return $this->sendResponse(CartResource::collection($cart_info),
        'تم ارسال جميع الاوردرات المطلوب تنفيذها');
    }

    public function client_cart(){

        $IdClient = Auth::guard('client')->user()->id;
        $cart_info = Order::select('*')->where([
                ['check', '=', 0],
                ['client_id', '=', $IdClient]
            ])->get();
            return $this->sendResponse(CartResource::collection($cart_info),
            'تم ارسال  اوردرات المستخدم  ');

    }

    public function store(Request $request){

        $input = $request->all();
        $validator = Validator::make($input,[
            // this client_id will taken by system automatically
            'client_id'     => 'nullable|numeric',
            'client_name'   => 'nullable|string',
            'client_phone'  => 'nullable|string',
            'new_address'   => 'string|nullable',
           // 'total'         => 'required|numeric',
           // 'payment_method'=> 'required|string',
            'status'        => 'boolean',
            'check'         => 'boolean',
            'Payment_Date'  => 'string|nullable',
            'user_id'       => 'nullable|numeric|exists:users,id',
            //'product_id'    => 'required|numeric|exists:products,id',


        ]);

        if ($validator->fails())
        {
            return $this->sendError('Please validate error' ,$validator->errors() );
        }

        $IdClient = Auth::guard('client')->user()->id;

        $order = Order::create([
            'client_id'    => $IdClient,
            'client_name'  => $request->client_name,
            'client_phone' => $request->client_phone,
            'new_address'  => $request->new_address,
            'total'        => $request->total,
            'payment_method' => $request->payment_method,
            'status'         => $request->status,
            'check'          => $request->check,
            'Payment_Date'   => $request->Payment_Date,
            'user_id'        => $request->user_id, // will add by admin in update function

        ]);

        $order_ids = Order::find($order_id);
        $order_ids->products()->attach($product_id);


        return $this->sendResponse(new CartResource($order) ,'تم اضافة الاوردر بنجاح ' );
    }

    public function update($id,Request $request){

        $input = $request->all();
        $validator = Validator::make($input,[
            'client_name'   => 'nullable|string',
            'client_phone'  => 'nullable|string',
            'new_address'   => 'nullable|string',
            'total'         => 'nullable|numeric',
            'payment_method'=> 'nullable|string',
            'status'        => 'boolean',
            'check'         => 'boolean',
            'Payment_Date'  => 'nullable|string',
            'user_id'       => 'required|numeric|exists:users,id',
            'product_id'    => 'nullable|numeric|exists:products,id',
        ]);

        if ($validator->fails())
        {
            return $this->sendError('Please validate error' ,$validator->errors() );
        }

        $order = Order::findOrFail($id);

        $order->client_name     = $request->client_name;
        $order->client_phone    = $request->client_phone;
        $order->new_address     = $request->new_address;
        $order->total           = $request->total;
        $order->payment_method  = $request->payment_method;
        $order->status          = $request->status;
        $order->check           = $request->check;
        $order->Payment_Date    = $request->Payment_Date;
        $order->user_id         = $request->user_id;
        $order->product_id      = $request->product_id;

        $order->save();
        return $this->sendResponse(new CartResource($order) ,'تم تعديل بيانات الاوردر بنجاح' );

    }

    public function destroy($id){

        $order = Order::findOrFail($id);
        if ($order != null) {
            $order->delete();
            return $this->sendResponse(new CartResource($order)  ,'تم حذف الاوردر بنجاح' );
        }
        else{
            return $this->sendError('الاوردر غير موجود');
        }

    }


}
