<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalEntriesTable extends Migration
{
    public function up()
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('journalable_id');
            $table->string('journalable_type');
            $table->date('date');
            $table->unsignedDecimal('amount', 20, 4);
            $table->enum('debit_credit', ['debit', 'credit']);
            $table->string('memo')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('journal_entries');
    }
}
