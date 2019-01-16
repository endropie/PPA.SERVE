<?php

namespace App\Models\Accounting;

use App\Models\Model;

class Account extends Model
{
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function subAccounts()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function parentAccount()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function getIsParentAttribute()
    {
        return $this->hasMany(Account::class, 'parent_id')->count() > 0 ? true : false;
    }

    public function accountTree()
    {
        $this->amount = 0;
        $this->amount_credit= 0;
        $this->amount_debit = 0;
        
        if (sizeof($this->subAccounts))
        {
            foreach ($this->subAccounts as $subAccount)
            {
                $this->subAccountTree();
            }
        }
        else
        {
            $this->accountType;
            $this->is_parent = false;

            $this->amount = $this->total();
            $this->amount_credit = $this->sumCredit();
            $this->amount_debit  = $this->sumDebit();
        }

        return ;
    }

    public function subAccountTree()
    {
        $this->amount = 0;
        $this->amount_credit= 0;
        $this->amount_debit = 0;
        
        if (sizeof($this->subAccounts))
        {
            foreach ($this->subAccounts as $subAccount)
            {
                $this->accountType;
                $this->is_parent = true;

                $tree = $subAccount->subAccountTree();
                $this->amount += $tree->amount;
                $this->amount_credit += $tree->amount_credit;
                $this->amount_debit  += $tree->amount_debit;
            }
        }
        else
        {
            $this->accountType;
            $this->is_parent = false;

            $this->amount = $this->total();
            $this->amount_credit = $this->sumCredit();
            $this->amount_debit  = $this->sumDebit();
        }

        return $this;
    }

    public function sumDebit()
    {
        $total = 0;
        foreach ($this->journalEntries as $entry)
        {
            if ($entry->debit_credit == 'debit')
            {
                $total += $entry->amount;
            }            
        }

        return $total;
    }

    public function sumCredit()
    {
        $total = 0;
        foreach ($this->journalEntries as $entry)
        {
            if ($entry->debit_credit == 'credit')
            {
                $total += $entry->amount;
            }
        }

        return $total;
    }

    public function total()
    {
        return $this->sumDebit() - $this->sumCredit();
    }

    public function totalAll()
    {
        $total = 0;
        
        if (sizeof($this->subAccounts))
        {
            foreach ($this->subAccounts as $subAccount)
            {
                $total += $subAccount->totalAll();
            }
        }
        else
        {
            $total = $this->total();
        }

        return $total;
    }
}
