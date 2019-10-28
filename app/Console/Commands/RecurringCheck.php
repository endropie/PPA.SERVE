<?php

namespace App\Console\Commands;

use App\Models\Common\Recurring;
use Illuminate\Console\Command;

class RecurringCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for recurring';

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
        $this->today = now()->today();

        $recurrings = Recurring::all();
        foreach ($recurrings as $recurring) {
            foreach ($recurring->schedule() as $recur) {
                $started_at = now()->parse($recurring->started_at);
                $recur_date = now()->parse($recur->getStart()->format('Y-m-d'));

                $model = $recurring->recurable;
                // BREAK => The Rocord has not relation with Recurring Model.
                if (!$model) break;

                // BREAK => today is started recurring.
                if ($this->today->eq($started_at)) break;
                // CONTINUE => today is not on the list recur date.
                if ($this->today->ne($recur_date)) continue;


                // BREAK => The record has recurring.
                // Inject "hasRecurring" Method in model.
                if ($model->hasRecurring($recur_date)) break;

                // Duplicate method => "Bkwld\Cloner\Cloneable" Package.
                $model->duplicate();
                break;
            }
        }
    }
}
