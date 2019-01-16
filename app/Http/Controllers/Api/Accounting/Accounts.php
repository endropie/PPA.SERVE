<?php

namespace App\Http\Controllers\Api\Accounting;

// use Illuminate\Http\Request;
use App\Http\Requests\Accounting\Account as Request;
use App\Http\Controllers\ApiController;

use App\Models\Accounting\Account;

class Accounts extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $accounts = Account::filterable()->get();
                $accounts->map(function ($value) {

                    return $value;
                });
                $accounts->makeHidden(['created_at','updated_at']);
                break;

            case 'parents':
                $accounts = Account::filterable()->orderBy('number','asc')->get();
                
                $accounts->map(function ($value) {
                    $value->setAppends(['is_parent']);
                    $value->has_journal_entries = $value->journalEntries()->count();
                    return false;
                });
                $accounts->makeHidden(['subAccounts','journalEntries','created_at','updated_at']);
                break; 

            case 'entries':
                $accounts = [];
                $lists = Account::filterable()->orderBy('number','asc')->get();
                    
                $lists->makeHidden(['created_at','updated_at']);

                foreach ($lists as $value) {
                    if(!$value->is_parent) $accounts[] = $value;
                }
                break; 

            case 'tree':
                $accounts = Account::where('parent_id',0)->orderBy('number','asc')->get();
                $accounts->map(function ($value) {
                    $value->accountTree();
                    return $value;
                });
                $accounts->makeHidden(['journalEntries','created_at','updated_at']);
                break;

            default:
                $accounts = Account::with(['accountType'])->collect();                
                break;
        }

        return response()->json($accounts);
    }

    public function create()
    {
        $account = new Account();
        $account->number = null;
        $account->name = null;
        $account->parent_id = null;
        $account->account_type_id = null;

        return response()->json($account);
    }

    public function store(Request $request)
    {
        $account = new Account();
        $account->number    = $request->number;
        $account->name      = $request->name;
        $account->parent_id = $request->parent_id;
        $account->account_type_id = $request->account_type_id;

        $account->save();

        return response()->json($account);
    }

    public function show($id)
    {
        if($id == 'create') return $this->create();

        $account = Account::with(['accountType'])->findOrFail($id);
        $account->is_parent = $account->is_parent;
        $account->has_journal_entries = $account->journalEntries()->count();
        $account->child_account_ids = $this->getChildArrays($account);

        $account->makeHidden(['subAccounts']);

        return response()->json($account);
    }

    private function getChildArrays($account)
    {
        $childs = [];
        if($account->subAccounts)
        {
            foreach ($account->subAccounts as $value) {
                $childs[] = $value->id;
                $grandChild = $this->getChildArrays($value);
                $childs = array_merge($childs, $grandChild);
            }
        }
        
        return $childs;
    }

    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $account->number    = $request->number;
        $account->name      = $request->name;
        $account->parent_id = $request->parent_id;
        $account->account_type_id = $request->account_type_id;

        $account->save();

        return response()->json($account);
    }

    public function destroy($id)
    {
        //
    }
}
