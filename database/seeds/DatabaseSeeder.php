<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(TestingTableSeeder::class);
        $this->call(DefaultTableSeeder::class);
        $this->call(ProvinceTableSeeder::class);
    }
}
