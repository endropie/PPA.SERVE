<?php
namespace App\Http\Controllers\Api\Common;

use App\Filters\Common\Rute as Filter ;
use App\Http\Requests\Common\Rute as Request;
use App\Http\Controllers\ApiController;
use App\Models\Common\Rute;

class Rutes extends ApiController
{
    public function index(Filter $filter)
    {
        switch (request('mode')) {
            case 'all':
                $rutes = Rute::filter($filter)->get();
                break;

            case 'datagrid':
                $rutes = Rute::with('rute_customers')->filter($filter)->get();

                break;

            default:
                $rutes = Rute::with('rute_customers')->filter($filter)->collect();
                break;
        }

        return response()->json($rutes);
    }

    public function store(Request $request)
    {
        $this->DATABASE::beginTransaction();


        $rute = Rute::create($request->all());

        foreach ($request->rute_customers as $row) {
            $rute->rute_customers()->create($row);
        }

        $rute->setCommentLog("Rute [$rute->name] has been created!");

        $this->DATABASE::commit();

        return response()->json($rute);
    }

    public function show($id)
    {
        $rute = Rute::with(['rute_customers.customer'])->findOrFail($id);

        $rute->append(['has_relationship']);

        return response()->json($rute);
    }

    public function update(Request $request, $id)
    {
        $this->DATABASE::beginTransaction();

        $rute = Rute::findOrFail($id);

        $rute->update($request->input());

        $notIn = collect($request['rute_customers'])->whereNotNull('id')->pluck('id');

        $rute->rute_customers()->whereNotIn('id', $notIn)->delete();

        foreach ($request->rute_customers as $row) {
            $rute->rute_customers()->updateOrcreate(['id' => $row['id'] ?? null], [
                'customer_id' => $row['customer_id'],
                'code' => $row['code'],
            ]);
        }

        $rute->setCommentLog("Rute [$rute->name] has been updated!");

        $this->DATABASE::commit();

        return response()->json($rute);
    }

    public function destroy($id)
    {
        $this->DATABASE::beginTransaction();

        $rute = Rute::findOrFail($id);

        if ($rute->is_relationship) $this->error("The data has RELATIONSHIP, is not allowed to be delete!");

        $rute->delete();

        $rute->setCommentLog("Rute [$rute->name] has been deleted!");

        $this->DATABASE::commit();

        return response()->json(['success' => true]);
    }
}
