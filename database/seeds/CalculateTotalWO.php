<?php

use App\Models\Common\Item;
use App\Models\Factory\WorkOrderItem;
use Illuminate\Database\Seeder;

class CalculateTotalWO extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $r = Item::whereHas('work_order_items', function ($q) {
            $q->whereHas('work_order', function($q) {
                $q->where('status', 'OPEN')->whereNull('main_id');
            });
        })
            ->get()
            ->each(function($e) {
                $e->setCalculateWO();
            });
    }
}
