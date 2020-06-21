<?php

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Database\Seeder;

class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        OrderDetail::truncate();
        Order::truncate();

        $order = Order::create([
            'customer_id' => 1,
            'order_state' => 0,
            'order_time' => DB::raw('NOW()'),
            'send_date' => '2020/01/01'
        ]);

        $totalPrice = 0;
        for($i = 1; $i <= 5; $i++) {
            $orderDetail = OrderDetail::create([
                'order_id' => $order->id,
                'item_name' => 'item_test' . $i,
                'item_qty' => $i,
                'unit_price' => $i * 100,
                'sum_price' => ($i * 100) * $i
            ]);

            $totalPrice += $orderDetail->sum_price;
        }

        $order->total_price = $totalPrice;
        $order->save();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
