<?php
namespace App\Api\Controllers\Common;

use App\Api\Controllers\ApiController;
use App\Api\Transformers\Common\Items as Transformer;
use App\Filters\Common\Item as Filter;
use App\Models\Common\Item;
use Illuminate\Http\Request;

class Items extends ApiController
{
    public function index (Filter $filter) {

        switch (request('mode')) {
            case 'limitation':
                $items = Item::filter($filter)->limitation();
                break;

            default:
                $items = Item::filter($filter)->pagination();
                break;
        }

        return method_exists($items, 'hasPages')
        ? $this->response->paginator($items, new Transformer(), ['key' => 'data'])
        : $this->response->collection($items, new Transformer());
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Request $request, $id)
    {

        $item = Item::findOrFail($id);

        $transformer = new Transformer();
        return $this->response->item($item, $transformer);
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
