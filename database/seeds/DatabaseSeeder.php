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
        if(app()->runningInConsole()) \Artisan::call('passport:install');

        $this->call(RolePermission::class);
        $this->call(Settings::class);
        $this->call(Basic::class);
    }
}
