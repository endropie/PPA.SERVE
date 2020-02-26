<?php
namespace App\Api\Transformers\Common;

use App\Api\Transformers\References\Units;
use App\Traits\TransformerLibrary;
use App\Models\Common\Item as Model;
use League\Fractal\TransformerAbstract;

class Items extends TransformerAbstract
{
    use TransformerLibrary;

    protected $allowFilled = true;

    protected $availableIncludes = [
        'unit', 'item_units', 'units'
    ];

    public function transform(Model $model)
    {
        return $this->setField([
            'id' => $model->id,
            'part_name' => $model->part_name,
            'part_number' => $model->part_number,
            'customer_code' => $model->customer_code,
        ], array_merge($model->attributesToArray(), [
            'unit_convertions' => $model->unit_convertions
        ]));
    }

    public function includeUnit(Model $model)
    {
        if ($unit = $model->unit) {
            return $this->item($unit, new Units());
        }
    }

    public function includeItemUnits(Model $model)
    {
        if ($item_units = $model->item_units) {
            return $this->collection($item_units, new Units);
        }
    }

    public function includeUnits(Model $model)
    {
        if ($units = $model->units) {
            return $this->collection($units, new Units());
        }
    }
}
