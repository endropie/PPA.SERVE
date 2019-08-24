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
        $this->call(Basic::class);
        $this->call(ImportDB::class);
        $this->call(Settings::class);
    }
}
