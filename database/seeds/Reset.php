<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Reset extends Seeder
{
	public function run()
    {
        DB::beginTransaction();

        DB::table('opname_vouchers')->truncate();
        DB::table('opname_stocks')->truncate();
        DB::table('opnames')->truncate();
        $this->command->warn('TRUNCATE opname stock tables');

        DB::commit();
    }

}
