<?php

use App\Models\Factory\WorkProduction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UniqueNumberFix extends Seeder
{
	public function run()
    {
        DB::beginTransaction();
        $double = DB::table('work_productions')->select('number')->groupBy('number')->havingRaw('COUNT(number) > 1')->get();

        foreach ($double as $i => $production) {
            $rows = WorkProduction::where('number', $production->number)->get();
            foreach ($rows as $key => $row) {
                $row->number .= ".". ($key+1);
                $row->save();
            }
            echo "DOUBLE $production->number (". ($i+1) ." of ". $double->count() .")\n";
        }

        // DB::rollback(); print("DB::ROLLBACK\n");

        DB::commit(); print("DB::COMMIT\n");
    }

}
