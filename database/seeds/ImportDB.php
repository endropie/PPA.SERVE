<?php

use Illuminate\Database\Seeder;

class ImportDB extends Seeder
{
	public function run()
    {

        // DB::disableQueryLog();
        $directory = base_path('database/seeds/SQL');

        $db = DB::connection()->getPdo();
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        try {
            $db->exec(file_get_contents("$directory/obj_brands.sql"));
            $db->exec(file_get_contents("$directory/obj_colors.sql"));
            $db->exec(file_get_contents("$directory/obj_customers.sql"));
            $db->exec(file_get_contents("$directory/obj_departments.sql"));
            $db->exec(file_get_contents("$directory/obj_positions.sql"));
            $db->exec(file_get_contents("$directory/obj_lines.sql"));
            $db->exec(file_get_contents("$directory/obj_vehicles.sql"));
            $db->exec(file_get_contents("$directory/obj_specifications.sql"));
            $db->exec(file_get_contents("$directory/obj_specification_details.sql"));
            $db->exec(file_get_contents("$directory/obj_employees.sql"));
            $db->exec(file_get_contents("$directory/obj_items.sql"));
            $db->exec(file_get_contents("$directory/obj_item_prelines.sql"));
            $db->exec(file_get_contents("$directory/obj_item_units.sql"));

        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->info($e->getMessage());
            dd($e->getMessage());
        }
	}
}
