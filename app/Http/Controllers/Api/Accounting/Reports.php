<?php

namespace App\Http\Controllers\Api\Accounting;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

use App\Models\Accounting\Account;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalEntry;

class Reports extends ApiController
{
    public function index()
    {
        // Code Here..
    }

    public function viewProfitLoss()
    {
        $report = [
            // 'ALL'        => $this->getAccoutBalance(),
            'income'        => $this->getAccoutBalance(12),
            'cogs'          => $this->getAccoutBalance(13),
            'expense'       => $this->getAccoutBalance(14),
            'otherIncome'   => $this->getAccoutBalance(15),
            'otherExpense'  => $this->getAccoutBalance(16),
        ];

        return response()->json($report);
    }

    public function viewBalanceSheet()
    {

        $report = [
            // Profit & Loss
            'income'        => $this->getAccoutBalance(12),
            'cogs'          => $this->getAccoutBalance(13),
            'expense'       => $this->getAccoutBalance(14),
            'otherIncome'   => $this->getAccoutBalance(15),
            'otherExpense'  => $this->getAccoutBalance(16),

            // Activa
            'current_asset' => $this->getAccoutBalance([1,2,3,4]), // cashBank, accountReceivable, inventory, otherCurrentAsset
            'fixed_asset'  => $this->getAccoutBalance([5,6,7]), // fixedAsset, accumulatedDepresiation, otherFixedAsset

            // Passiva
            'current_liabilities'  => $this->getAccoutBalance([8,9]), // currentDebt, otherCurrentDebt
            'longterm_liabilities' => $this->getAccoutBalance(10), // longTermDebt
            'equity'               => $this->getAccoutBalance(11), // equity

        ];
        return response()->json($report);
    }

    private function getAccoutBalance($type = null)
    {
        if(is_array($type)){
            $accounts = Account::where('parent_id', 0)->whereIn('account_type_id',$type)->get();    
        }
        elseif(is_integer($type)){
            $accounts = Account::where('parent_id', 0)->where('account_type_id',$type)->get();    
        }else{
            $accounts = Account::where('parent_id', 0)->get();
        }

        $total = 0;
        
        foreach ($accounts as $account) {
            if(count($account->subAccounts) > 0){
                $account->amount = $this->getSubAccoutsBalance($account->subAccounts);
            }
            else {
                $account->amount = $account->journalEntries->sum(function ($entri) {
                    if ($this->requestFilter($entri))
                    {
                        if($entri['debit_credit'] == 'debit') return $entri['amount'];
                        if($entri['debit_credit'] == 'credit') return $entri['amount'] * (-1);
                    }
                    return false;
                });
            }
            $total += $account->amount;
        }

        $accounts->makeHidden(['journalEntries', 'created_at','updated_at']);
       
        return ['accounts' => $accounts, 'total' => $total];
    }

    private function getSubAccoutsBalance($accounts) 
    {
        $amount = 0;

        foreach ($accounts as $account) {
            if(count($account->subAccounts) > 0){
                $account->amount = $this->getSubAccoutsBalance($account->subAccounts);
            }
            else {
                $account->amount = $account->journalEntries->sum(function ($entri) {
                    
                    if ($this->requestFilter($entri))
                    {
                        if($entri['debit_credit'] == 'debit') return $entri['amount'];
                        if($entri['debit_credit'] == 'credit') return $entri['amount'] * (-1);
                    }
                    return false;
                });

                $accounts->makeHidden(['subAccounts']);
            }
            
            $amount += $account->amount;
        }

        $accounts->makeHidden(['journalEntries', 'created_at','updated_at']);
       
        return $amount;
    }

    private function requestFilter($entri) 
    {
        // $date = app($entri['journalable_type'])::find($entri['journalable_id'])->date;

        if(request('date_range')){
            $dateRange = explode(',', request('date_range'), 2);
            
            if($entri->date < $dateRange[0] || $entri->date > $dateRange[1]) {
                return false;
            }
        }
        
        if(request('upto_date')){
            $upto_date = request('upto_date');
            
            if($entri->date > $upto_date) {
                return false;
            }
        }

        return true;
    }
}
