<?php
namespace App\Api\Transformers\Warehouses;

use App\Api\Transformers\Common\Items;
use App\Api\Transformers\References\Units;
use App\Api\Transformers\UserTransformer;
use App\Traits\TransformerLibrary;
use App\Models\Warehouse\OpnameVoucher as Model;
use League\Fractal\TransformerAbstract;

class OpnameVouchers extends TransformerAbstract
{
    use TransformerLibrary;

    protected $allowFilled = true;

    protected $availableIncludes = [
        'created_user', 'item', 'unit',
    ];

    public function transform(Model $model)
    {
        return $this->setField([
            'id' => $model->id,
            'number' => $model->number,
        ], $model->attributesToArray());
    }

    public function includeUserBy(Model $model)
    {
        if ($created_user = $model->created_user) {
            return $this->item($created_user, new UserTransformer());
        }
    }

    public function includeItem(Model $model)
    {
        if ($item = $model->item) {
            return $this->item($item, new Items());
        }
    }

    public function includeUnit(Model $model)
    {
        if ($unit = $model->unit) {
            return $this->item($unit, new Units());
        }
    }
}
