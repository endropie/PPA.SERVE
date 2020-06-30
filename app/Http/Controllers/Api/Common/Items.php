<?php

namespace App\Http\Controllers\Api\Common;

use App\Filters\Common\Item as Filters;
use App\Http\Requests\Common\Item as Request;
use App\Http\Controllers\ApiController;
use App\Models\Common\Item;
use App\Models\Common\ItemStockable;

class Items extends ApiController
{
    public function index(Filters $filters)
    {
        switch (request('mode')) {
          case 'all':
            $items = Item::with(['item_prelines','item_units','unit'])->filter($filters)->get();
          break;

          case 'datagrid':
            $items = Item::with(['item_prelines','item_units', 'brand', 'customer', 'specification'])->filter($filters)->latest()->get();

          break;

          case 'itemstock':
            $items = Item::filter($filters)->get(['id'])->map->append('totals');

          break;

          case 'stockables':
          $items = ItemStockable::whereHas('item', function ($q) use ($filters) {
            return $q->filter($filters);
          })->get();

          break;

          default:
            $items = Item::with(['item_prelines','item_units', 'brand', 'customer', 'specification'])
              ->filter($filters)->collect();
          break;
        }

        return response()->json($items);
    }

    public function stockables(Filters $filters)
    {
        switch (request('mode')) {
          case 'all':
            $items = ItemStockable::whereHas('item', function ($q) use ($filters) {
                return $q->filter($filters);
            })->get();
          break;

          default:
            $items = ItemStockable::with('item.unit')->whereHas('item', function ($q) use ($filters) {
                return $q->filter($filters);
            })->latest()->collect();

            $items->getCollection()->transform(function($item) {
                $item->append(['base_data']);
                return $item;
            });

          break;
        }

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();

        if(!strlen($request->code)) {
            $code = Item::withSampled()->select('id')->max('id');
            $code = str_pad($code + 1, 6, '0', STR_PAD_LEFT);
            $request->merge(['code' => $code]);
        }

        $item = Item::create($request->all());

        $preline_rows = $request->item_prelines ?? [];
        for ($i=0; $i < count($preline_rows); $i++) {
            // create pre production on the item updated!
            if ($i == 0) $preline_rows[$i]["ismain"] = 1;
            if ($preline_rows[$i]['line_id']) $item->item_prelines()->create($preline_rows[$i]);
        }

        $unit_rows = $request->item_units ?? [];
        for ($i=0; $i < count($unit_rows); $i++) {
            // create item units on the item updated!
            if ($unit_rows[$i]['unit_id']) $item->item_units()->create($unit_rows[$i]);
        }

        if(!$item->code) $item->update(['code' => $item->id]);

        $this->DATABASE::commit();
        return response()->json($item);
    }

    public function show($id)
    {
        $this->DATABASE::beginTransaction();
        $with = [
            'customer','brand','category_item', 'type_item', 'size', 'unit',
            'item_stockables'
        ];
        $item = Item::withSampled()->with(array_merge($with, ['item_prelines.line', 'item_units']))->findOrFail($id);
        $item->is_editable = (!$item->is_related);

        $this->DATABASE::commit();
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $item = Item::withSampled()->findOrFail($id);

        $item->update($request->input());

        // Delete pre production on the item updated!
        $item->item_prelines()->delete();
        $preline_rows = $request->item_prelines;
        for ($i=0; $i < count($preline_rows); $i++) {
            // create pre production on the item updated!
            if($i == 0) $preline_rows[$i]["ismain"] = 1;
            $item->item_prelines()->create($preline_rows[$i]);
        }

        // Delete item units on the item updated!
        $item->item_units()->delete();
        $unit_rows = $request->item_units;
        for ($i=0; $i < count($unit_rows); $i++) {
            // create item units on the item updated!
            $item->item_units()->create($unit_rows[$i]);
        }

        if ($request->isRegulerRequest) {
            $item->sample_moved_by = auth()->user()->id;
            $item->sample_moved_at = now();
            $item->save();
        }

        $this->DATABASE::commit();
        return response()->json($item);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $item = Item::findOrFail($id);

        if ($item->is_relationship) $this->error("CODE:$item->code has data relation, Delete not allowed!");

        $item->item_prelines()->delete();
        $item->item_units()->delete();
        $item->delete();

        $this->DATABASE::commit();
        return response()->json(array_merge($item->toArray(), ['success' => true]));
    }

    public function sampleValidation($id)
    {
        $item = Item::withSampled()->findOrFail($id);

        if (!$item->sample) return $this->error("Part [$item->code] $item->part_name is not sample!");

        if (!$item->sample_moved_by) return $this->error("Part [$item->code] $item->part_name is not sample moved!");

        if ($item->sample_validated_by) return $this->error("Part [$item->code] $item->part_name has been validated!");

        if (!$item->item_prelines->count()) return $this->error("Part [$item->code] $item->part_name has not prelines!");

        if (!$item->unit) return $this->error("Part [$item->code] $item->part_name has not unit!");

        if (!$item->specification) return $this->error("Part [$item->code] $item->part_name has not specification!");

        $item->sample_validated_by = auth()->user()->id;
        $item->sample_validated_at = now();
        $item->sample = 0;

        $item->save();

        return response()->json($item);
    }

    public function push($id)
    {
        if ($id === 'all') {
            $items = Item::whereNull('accurate_model_id')->get();
            return $items->map(function($item) {
                $push = $item->accurate()->push();
                return collect($push)->except('r');
            });
        }
        else {
            $item = Item::findOrFail($id);
            return $item->accurate()->push();
        }
    }
}
