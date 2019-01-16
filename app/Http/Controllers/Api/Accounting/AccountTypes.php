<?php

namespace App\Http\Controllers\Api\Accounting;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

use App\Models\Accounting\AccountType;

class AccountTypes extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $accountTypes = AccountType::filterable()->get();
                $accountTypes->makeHidden(['created_at','updated_at']);
                break;

            default:
                $accountTypes = Account::with(['accountType'])->collect();                
                break;
        }

        return response()->json($accountTypes);
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        $accountType = AccountType::findOrFail($id);

        return response()->json($accountType);
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
