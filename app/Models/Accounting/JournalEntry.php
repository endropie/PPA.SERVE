<?php

namespace App\Models\Accounting;

use App\Models\Model;

class JournalEntry extends Model
{
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function journalable()
    {
        return $this->morphTo();
    }

    public function getSourceNumberAttribute()
    {
        // If coloumn of journalable sources isn't "number" set define here...
        if($this->journalable_type == ''){  
            return null;
        }

        // Default: coloumn of journalable sources is "number"
        return app($this->journalable_type)->find($this->journalable_id)->number;
    }

    public function getAmountDebitAttribute()
    {
        return $this->debit_credit == 'debit' ? $this->amount : 0;
    }

    public function getAmountCreditAttribute()
    {
        return $this->debit_credit == 'credit' ? $this->amount : 0;
    }
}
