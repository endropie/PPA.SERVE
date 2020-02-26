<?php
namespace App\Api\Transformers\References;

use App\Traits\TransformerLibrary;
use App\Models\Reference\Unit as Model;
use League\Fractal\TransformerAbstract;

class Units extends TransformerAbstract
{
    use TransformerLibrary;

    public function transform(Model $model)
    {
        return $this->setField($model->attributesToArray());
    }
}
