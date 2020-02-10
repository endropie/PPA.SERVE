<?php
namespace App\Http\Controllers\Api\References;

use App\Filters\Filter;
use App\Http\Requests\Reference\Specification as Request;
use App\Http\Controllers\ApiController;
use App\Models\Reference\Specification;

use function RingCentral\Psr7\str;

class Specifications extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $specifications = Specification::filter($filter)->get();
                break;

            case 'datagrid':
                $specifications = Specification::with(['color'])->filter($filter)->get();

                break;

            default:
                $specifications = Specification::with('color')->filter($filter)->collect();
                break;
        }

        return response()->json($specifications);
    }

    public function store(Request $request)
    {
        $code = (int) Specification::max('code');
        $code = str_pad(strval($code+1), 3, '0', STR_PAD_LEFT);
        $request->merge(['code'=>$code]);
        $specification = Specification::create($request->all());

        // Delete pre production on the item updated!
        $specification->specification_details()->delete();

        $rows = $request->specification_details;
        for ($i=0; $i < count($rows); $i++) {
            if(isset($rows['thick'])) {
                // create pre production on the item updated!
                $specification->specification_details()->create($rows[$i]);
            }
        }

        return response()->json($specification);
    }

    public function show($id)
    {
        $specification = Specification::with('specification_details')->findOrFail($id);
        $specification->append(['has_relationship']);

        return response()->json($specification);
    }

    public function update(Request $request, $id)
    {
        $specification = Specification::findOrFail($id);

        $specification->update($request->input());

        // Delete pre production on the item updated!
        $specification->specification_details()->delete();

        $details = $request->specification_details;
        for ($i=0; $i < count($details); $i++) {

            // create pre production on the item updated!
            $specification->specification_details()->create($details[$i]);
        }

        return response()->json($specification);
    }

    public function destroy($id)
    {
        $specification = Specification::findOrFail($id);
        $specification->delete();

        return response()->json(['success' => true]);
    }
}
