<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomingValidationsTable extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('partialidate_allowed')->default(0)->after('order_mode');
        });

        Schema::create('incoming_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incoming_good_id');
            $table->date('date');
            $table->text('description')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('incoming_validation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incoming_validation_id');
            $table->foreignId('incoming_good_item_id');
            $table->decimal('quantity');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('partialidate_allowed');
        });

        Schema::dropIfExists('incoming_validation_items');
        Schema::dropIfExists('incoming_validations');
    }
}
