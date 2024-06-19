<?php
namespace App\Http\Resources\Factory;

use App\Http\Resources\factory\WorkOrderItemResource;
use App\Http\Resources\Resource;

class WorkOrderResource extends Resource
{

    public function toArray($request)
    {
        return [
            $this->mergeAttributes(),
            $this->mergeInclude('shift', new Resource($this->resource->shift)),
            $this->mergeInclude('line', new Resource($this->resource->line)),
            $this->mergeInclude('work_order_items', WorkOrderItemResource::collection($this->resource->work_order_items))
        ];
    }
}
