<?php

namespace App\Http\Controllers\Api\Accounting;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

use App\Models\Accounting\Account;
use App\Models\Accounting\JournalVoucher;
use App\Models\Accounting\JournalEntry;

class JournalVouchers extends ApiController
{
    public function index()
    {
        switch (request('mode')) {
            case 'all':            
                $journals = Journal::filterable()->get();
                $journals->map(function ($journal) {
                    $journal->setAppends(['amount']);
                    return $journal;
                });
                $journals->makeHidden(['created_at','updated_at']);
                break;

            default:
                $journals = Journal::collect();

                $journals->map(function ($journal) {
                    $journal->setAppends(['amount']);
                    return $journal;
                });
                break;
        }

        return response()->json($journals);
    }

    public function create()
    {
        $account = new Journal();
        $account->number = null;
        $account->date = date('Y-m-d');
        $account->description = null;
        $account->journal_entries = [];
        
        return response()->json($account);
    }

    public function store(Request $request)
    {
        $debit = 0;
        $credit = 0;

        foreach($request->journalEntries as $key => $row)
        {
            foreach($row as $index => $value)
            {
                switch ($key)
                {
                    case 'debit':
                        if($value > 0)
                        {
                            $journalEntryRows[$index]['debit_credit'] = $key;
                            $journalEntryRows[$index]['amount'] = $value;
                            $debit += $value;
                        }
                        break;

                    case 'credit':
                        if($value > 0)
                        {
                            $journalEntryRows[$index]['debit_credit'] = $key;
                            $journalEntryRows[$index]['amount'] = $value;
                            $credit += $value;
                        }
                        break;
                    
                    default:
                        $journalEntryRows[$index][$key] = $value;
                        break;
                }
            }
        }

        foreach($journalEntryRows as $row)
        {
            if(isset($row['account_id']) && isset($row['amount']))
            {
                $journalEntries[] = $row;
            }
        }

        $journal = new Journal;

        $journal->date = $request->date;
        $journal->description = $request->description;

        $journal->save();

        foreach ($journalEntries as $entry)
        {
            $account = Account::find($entry['account_id']);

            $journal->accounts()->save($account, ['debit_credit' => $entry['debit_credit'], 'amount' => $entry['amount'], 'memo' => $entry['memo']]);
        }

        return redirect()->route('admins.journals.index');
    }

    public function show($id)
    {
        if($id == 'create') return $this->create();

        $journal = Journal::findOrFail($id);
        foreach ($journal->journalEntries as $entries) {
            $entries->account;
            $entries->setAppends(['amount_credit','amount_debit']);
        }

        $journal->isForm = [
            'edit' => true,
            'relations' => []
        ];

        return response()->json($journal);
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        $journal = Journal::findOrFail($id);

        $journal->journalEntries()->delete();
        $journal->delete();

        return response()->json(['success'=>true]);
    }

    public function entries()
    {
        $entries = JournalEntry::collect();

        $entries->map(function($entri){
            $entri->account;
            $entri->setAppends(['source_number', 'amount_debit', 'amount_credit']);
            return $entri;
        });

        return response()->json($entries);
    }

    public function setImport(Request $request)
    {
        // dd($request->data);
       \DB::beginTransaction();

        $count_etries = 0;
        try 
        {            
            foreach ($request->data as $key => $row) {
                
                if($row['total_debit'] != $row['total_credit'] && ($row['total_debit'] == 0 || $row['total_credit'] == 0))
                {
                    \DB::rollback();
                    return response()->json(['success'=>false, 'message'=> 'Journal['. $row['number'] .'] total amount invalid!']); 
                }

                if($row['id'])
                {
                    $journal = Journal::firstOrNew(['id'=>$row['id']]);
                }else
                {
                    $journal = new Journal();
                }

                if($row['date']) $journal->date = $row['date'];
                if($row['number']) $journal->number = $row['number'];
                if($row['description']) $journal->description = $row['description'];

                $journal->save();
                $journal->journalEntries()->delete();

                foreach ($row['entries'] as $key => $rowEntry) {
                    $account = Account::find($rowEntry['account_id']);

                    if(empty($account->id) || $account->is_parent){
                        \DB::rollback();
                        return response()->json(['success'=>false, 'message'=> 'Invalid account[id] of entries Journal['. $row['number'] .']!']);
                    }
                    
                    $entry = new JournalEntry();
                    $entry->date = $journal->date;
                    $entry->account_id   = $account->id;

                    $entry->memo         = $rowEntry['memo'];
                    $entry->amount       = $rowEntry['amount'];
                    $entry->debit_credit = $rowEntry['debit_credit'];

                    $journal->journalEntries()->save($entry);
                    $count_etries ++;
                }
            }

            \DB::commit();
        }
        catch (\Throwable $e) {
            
            \DB::rollback();
            
            if($e){
                return response()->json([
                    'success' => false,
                    'errors' => $e,
                    'message' => $e->errorInfo ?? $e->getMessage(),
                    'code'    => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    // 'previous' => $e->getPrevious(),
                    // 'trace' => $e->getTrace(),
                    // 'traceAsString' => $e->getTraceAsString(),
                    // '__toString' => $e->__toString()
                ]);
            }
            else throw $e;
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Import Success: '. count($request->data).' Journals, '. $count_etries .' entries',
        ]);
    }
}
