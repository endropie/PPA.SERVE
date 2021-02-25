<?php

use App\Models\Factory\WorkProduction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UniqueNumberFix extends Seeder
{
	public function run()
    {
        // DB::beginTransaction();

        $total = (int) WorkProduction::count();
        $size = 5;
        $limit = ($total / $size)+1;

        echo "LIMIT $limit\n";

        for ($i=0; $i < $limit; $i++) {

            foreach (WorkProduction::offset($size*$i)->take($size)->get() as $production) {

                $production->refresh();

                $get = WorkProduction::where('id', '<>', $production->id)
                    ->where('number', $production->number)
                    ->get();

                foreach ($get as $key => $row) {
                    $row->number .= ".". ($key+2);
                    $row->save();

                    echo "PRO $row->number\n";
                }

                $get->number .= ".". (1);
                $get->save();

                echo "PRO $production->id [". $get->count() ." ]\n";
            }
        };

        // DB::rollback(); print("DB::ROLLBACK\n");

        // DB::commit(); print("DB::COMMIT\n");

        print("$size > $total ->(". ($total+$size) .")\n");
    }

}
