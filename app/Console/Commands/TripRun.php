<?php

namespace App\Console\Commands;

use App\Models\Income\Trip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TripRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trip:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running Daily trip scheduler';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $date = now()->yesterday();

        if (!Trip::where('date', $date->format('Y-m-d'))->count())
        {
            DB::transaction(function () use($date) {
                Trip::dailyScheduler($date, true);
            });
        }

    }
}
