<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetTransaction extends Seeder
{
	public function run()
    {
        DB::beginTransaction();
        Schema::disableForeignKeyConstraints();

        // DATA FOR TRANSACTION
        ## ====================

        DB::table('opname_vouchers')->truncate();
        DB::table('opname_stocks')->truncate();
        DB::table('opnames')->truncate();
        $this->command->warn('TRUNCATE opname stock tables');

        DB::table('incoming_goods')->truncate();
        DB::table('incoming_good_items')->truncate();
        $this->command->warn('TRUNCATE incoming_good tables');

        DB::table('pre_deliveries')->truncate();
        DB::table('pre_delivery_items')->truncate();
        DB::table('pre_delivery_schedules')->truncate();
        $this->command->warn('TRUNCATE pre_delivery tables');

        DB::table('outgoing_goods')->truncate();
        DB::table('outgoing_good_items')->truncate();
        $this->command->warn('TRUNCATE outgoing_good tables');

        DB::table('outgoing_good_verifications')->truncate();
        $this->command->warn('TRUNCATE outgoing_verification tables');

        DB::table('forecasts')->truncate();
        DB::table('forecast_items')->truncate();
        $this->command->warn('TRUNCATE forecast tables');

        DB::table('request_orders')->truncate();
        DB::table('request_order_items')->truncate();
        $this->command->warn('TRUNCATE request_order tables');

        DB::table('delivery_orders')->truncate();
        DB::table('delivery_order_items')->truncate();
        $this->command->warn('TRUNCATE delivery_order tables');

        DB::table('work_orders')->truncate();
        DB::table('work_order_items')->truncate();
        DB::table('work_order_item_lines')->truncate();
        $this->command->warn('TRUNCATE work_order tables');

        DB::table('work_productions')->truncate();
        DB::table('work_production_items')->truncate();
        $this->command->warn('TRUNCATE work_production tables');

        DB::table('packings')->truncate();
        DB::table('packing_items')->truncate();
        DB::table('packing_item_faults')->truncate();
        $this->command->warn('TRUNCATE packing tables');

        DB::table('schedule_boards')->truncate();
        DB::table('schedule_board_customers')->truncate();
        $this->command->warn('TRUNCATE schedule_board tables');

        ## RELATION FOR TRANSACTION
        DB::table('item_stocks')->truncate();
        $this->command->warn('TRUNCATE item_stocks table');

        DB::table('item_stockables')->truncate();
        $this->command->warn('TRUNCATE item_stockables table');

        DB::table('stateables')->truncate();
        $this->command->warn('TRUNCATE stateables table');

        DB::table('recurring')->truncate();
        $this->command->warn('TRUNCATE recurring table');

        Schema::enableForeignKeyConstraints();
        DB::commit();
    }

}
