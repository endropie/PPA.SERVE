<?php

namespace App\Models\Accounting;

use App\Models\Model;

class Journal extends Model
{
    // protected  $fillable = ['number', 'date', 'description'];

    public function journalEntries()
    {
        return $this->morphMany(JournalEntry::class, 'journalable')->orderBy('debit_credit','asc');
    }

    public function accounts()
    {
        return $this->morphToMany(Account::class, 'journalable', 'journal_entries');
    }

    public function sumDebit()
    {
        return $this->journalEntries->where('debit_credit', 'debit')->sum('amount');
    }

    public function sumCredit()
    {
        return $this->journalEntries->where('debit_credit', 'credit')->sum('amount');
    }

    public function getAmountAttribute()
    {
        return (double) $this->journalEntries()->where('debit_credit', 'debit')->sum('amount');
    }
}
