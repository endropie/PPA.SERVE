<?php
namespace App\Http\Resources\Factory;

use App\Http\Resources\Common\ItemResource;
use App\Http\Resources\Resource;

class WorkOrderItemResource extends Resource
{

    public function toArray($request)
    {
        return [
            $this->mergeAttributes(),
            $this->mergeInclude('item', new ItemResource($this->resource->item)),
            $this->mergeInclude('unit', new Resource($this->resource->unit)),
            $this->mergeInclude('work_production_items', Resource::collection($this->resource->work_production_items)),
        ];
    }
}
