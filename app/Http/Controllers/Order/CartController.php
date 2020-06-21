<?php

namespace App\Http\Controllers\Order;

use App\Events\PurchaseEvent;
use App\Http\Controllers\Controller;
use App\Repositories\Tag\TagRepository;
use App\Services\ItemService;
use DB;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Stock;
use App\Models\Order;
use App\Models\OrderDetail;
use Auth;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class CartController extends Controller
{

    private $service;

    /**
     * ItemController constructor.
     * @param ItemService $service
     * @param TagRepository $tagRepository
     */
    public function __construct(ItemService $service)
    {
        $this->service = $service;
    }


    public function showMyCart(Request $request){

        $userid = Auth::user()->id ?? "XXXX";

        $data = (object) [
            'getCartItemsUrl' => route('order.myCart.items'),
            'removeCartItemsUrl' => route('order.myCart.removeCartItems'),
            'buyCartItemsUrl' => route('order.myCart.buyCartItems'),
            'backUrl' => route('item.index'),
            'getCustomerUrl' => route('customers.show',  $userid ),
            'editCustomerUrl' => route('customer.edit',  $userid )
        ];

        return view('order.myCart', compact('data'));
    }

    public function getCartItems(Request $request){

        $cart = $request->session()->get('cart', []);

        if(count($cart) == 0){
            $data = (object)[
                'myCartItems' =>  null
            ];

            return response()->json($data);

        }

        $myCartItems = [];
        foreach($cart as $k => $v){

            $item = Item::where(['name' => $k])->first();
            $stock = Stock::where(['item_name' => $k])->first();

            if(!$item || !$stock){
                continue;
            }

            $myCartItems[] = (object)[
                'name' => $item->name,
                'display_name' => $item->display_name,
                'price' => $item->price,
                'thumbnail' => $item->thumbnail,
                'detailUrl' => route('item.detail', ['id' => $item->name]),
                'qty' => (int)$v['qty'],
                'hasStock' => ($stock->stock > 0 && $stock->stock >= $v['qty']) ? true : false
            ];

        }


        $data = (object)[
            'myCartItems' => $myCartItems
        ];

        return response()->json($data);
    }

    public function removeCartItems(Request $request){

        $removeCartItemsData = $request->get('removeCartItems');

        $cart = $request->session()->get('cart', []);

        $newCartItems = collect($cart)->filter(function($value, $key) use ($removeCartItemsData){
            return !in_array($key, $removeCartItemsData);
        })->toArray();

        session()->put('cart', $newCartItems);

        return response()->json(['newCartItems'=> $newCartItems]);

    }

    public function buyCartItems(Request $request){

        $buyCartItemsData = $request->get('buyCartItems');

        $cart = $request->session()->get('cart', []);


        $customer = Customer::where(['user_id' => Auth::id()])->first();
        if(!$customer){
            return response()->json(['result' => 'ng'], 404);
        }
        $customer_id = $customer->id ;
        $order = $this->buy($customer_id, $buyCartItemsData);


        $newCartItems = collect($cart)->filter(function($value, $key) use ($buyCartItemsData){
           $res = collect($buyCartItemsData)->contains('name', $key);
           return !$res;
        })->values()->toArray();

        session()->put('cart', $newCartItems);

        //メール送信
        // エラー発生時、クライアントには正常終了とみなす
        try{
            event(new PurchaseEvent(Auth::user(), $order));
        }catch(\Exception $e){
            Log::error($e->getMessage() . "\n" . $e->getTraceAsString());
        }

        return response()->json(['newCartItems'=> $newCartItems]);
    }


    private function buy($customerId, $items){

        DB::beginTransaction();
        try {
            $totalPrice = 0;

            $order = Order::create([
                'customer_id' => $customerId,
                'order_state' => '0',
                'order_time' => Carbon::now()
            ]);

            foreach($items as $k => $v){
                $name = $v['name'];
                $qty = $v['qty'];

                $this->minusStockAuto($name, $qty);

                $unitPrice = Item::where(['name' => $name])->first()->price;
                $sumPrice = $unitPrice * (int)$qty;
                $totalPrice += $sumPrice;

                OrderDetail::create([
                    'order_id' => $order->id,
                    'item_name' => $name,
                    'item_qty' => $qty,
                    'unit_price' => $unitPrice,
                    'sum_price' => $sumPrice,
                ]);
            }

            $order->refresh();
            $order->total_price = $totalPrice;
            $order->save();

            DB::commit();

            $order->refresh();
            return $order;

        } catch (\Exception $e) {
            DB::rollback();

            throw $e;

        }
    }

    private function minusStockAuto($name, $qty){

        //$itemStock = Stock::where(['name' => $name])->where('stock', '>', $qty)->first();
        //$itemStock->stock = $itemStock->stock - $qty;
        //$itemStock->save();
        $res = Stock::where(['item_name' => $name])
            ->where('stock', '>=', $qty)
            ->decrement('stock', $qty);

    }


}
