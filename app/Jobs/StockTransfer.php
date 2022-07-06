<?php

namespace App\Jobs;

use App\Extensions\CustomQueue\Dispatchable;
use App\Models\Common\Item;
use App\Models\Common\ItemStock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StockTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $item;
    protected $stock;
    protected $model;
    protected $number;
    protected $stockist;
    protected $exStockist;

    protected $allowStockLess;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Item $item, $model, $number, $stockist, $exStockist = null)
    {
        $this->item = $item;
        $this->model = $model;
        $this->number = $number;
        $this->stockist = $stockist;
        $this->exStockist = $exStockist;
        $this->allowStockLess = $this->item->allowTransferStockLess();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        if (!$this->item->enable && !$this->item->allowTransferDisabled) {
            abort(501, "PART [". $this->item->code. "] DISABLED");
        }

        $model = $this->model->fresh();

        if ($this->exStockist) {
            $exStockist = ItemStock::getValidStockist($this->exStockist);
            $exStock = $this->item->stocks()->firstOrCreate(['stockist' => $exStockist]);

            if (!$this->allowStockLess && round($exStock->total) <  round($this->number)) {
                $partName = $this->item->part_name;
                $partName .= $this->item->part_subname ? "(". $this->item->part_subname .")" : "";
                abort(511, "PART $partName - [$exStockist] STOCKLESS");
            }

            $exStock->decrement('total', $this->number);
            $exStock->save();

            $this->item->stockables()->create([
                'base_id' => $model->id,
                'base_type' => get_class($model),
                'unit_amount' => (-1) * ($this->number),
                'stockist' => $exStockist,
            ]);
        }

        if ($this->stockist) {
            $stockist = ItemStock::getValidStockist($this->stockist);
            $stock = $this->item->stocks()->firstOrCreate(['stockist' => $stockist]);
            $stock->increment('total', $this->number);

            $this->item->stockables()->create([
                'base_id' => $model->id,
                'base_type' => get_class($model),
                'unit_amount' => ($this->number),
                'stockist' => $stockist,
            ]);
        }
    }
}
