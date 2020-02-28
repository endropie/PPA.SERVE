<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Reset extends Seeder
{
	public function run()
    {
        DB::beginTransaction();
        Schema::disableForeignKeyConstraints();

        DB::table('opname_vouchers')->truncate();
        DB::table('opname_stocks')->truncate();
        DB::table('opnames')->truncate();
        $this->command->warn('TRUNCATE opname stock tables');

        Schema::enableForeignKeyConstraints();
        DB::commit();
    }

}
