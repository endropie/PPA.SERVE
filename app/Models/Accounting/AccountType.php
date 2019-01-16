<?php

namespace App\Models\Accounting;

use App\Models\Model;

class AccountType extends Model
{
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function total()
    {
        $total = 0;

        foreach ($this->accounts as $account)
        {
            $total += $account->total();
        }

        return abs($total);
    }
}
