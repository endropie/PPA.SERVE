<?php

namespace App\Jobs;

use App\Models\Common\Item;
use Illuminate\Bus\Queueable;
use App\Extensions\CustomQueue\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $item;
    protected $number;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Item $item, $number = 0, $loop = 0)
    {
        $this->loop = $loop;
        $this->item = $item;
        $this->number = $number;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        echo "\ntest: run";

        if ($this->number) sleep(10);
        $item = $this->item;
        $item->description =  $item->description. "[$this->number]";
        $item->save();

        echo "\ntest: [$item->id] done => ". $item->description;

    }

    public function getTest()
    {
        return "get-tes";
    }
}
