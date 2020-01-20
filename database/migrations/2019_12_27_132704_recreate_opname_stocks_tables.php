<?php

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateOpnameStocksTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('opname_stocks');
        Schema::dropIfExists('opname_stock_items');

        Schema::create('opnames', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('number');
            $table->integer('revise_id')->nullable();
            $table->integer('revise_number')->nullable();
            $table->string('status')->default('OPEN');

            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('opname_stocks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('item_id');
            $table->string('stockist');
            $table->float('init_amount');
            $table->float('final_amount')->nullable();

            $table->integer('created_by')->nullable();
            $table->bigInteger('opname_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('opname_id')
                ->references('id')->on('opnames')
                ->onDelete('CASCADE');
        });

        Schema::create('opname_vouchers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('number', 10);
            $table->integer('item_id');
            $table->string('stockist');
            $table->decimal('quantity');
            $table->integer('unit_id');
            $table->float('unit_rate')->default(1);
            $table->string('status')->default('OPEN');

            $table->bigInteger('opname_stock_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('opname_stock_id')
                ->references('id')->on('opname_stocks')
                ->onDelete('SET NULL');
        });

        // create Roles & Permissions
        $this->setPermiss();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opname_vouchers');
        Schema::dropIfExists('opname_stocks');
        Schema::dropIfExists('opnames');
    }

    protected function setPermiss () {
        $OpnameStockRole = Role::firstOrCreate(['name' => 'user.opname.stocks']);
        $OpnameStockRole->givePermissionTo(Permission::firstOrCreate(['name' => "opname-stocks-create"]));
        $OpnameStockRole->givePermissionTo(Permission::firstOrCreate(['name' => "opname-stocks-read"]));
        $OpnameStockRole->givePermissionTo(Permission::firstOrCreate(['name' => "opname-stocks-update"]));
        $OpnameStockRole->givePermissionTo(Permission::firstOrCreate(['name' => "opname-stocks-delete"]));
        Permission::firstOrCreate(['name' => "opname-stocks-void"]);
        Permission::firstOrCreate(['name' => "opname-stocks-validation"]);

        $OpnameVoucherRole = Role::firstOrCreate(['name' => 'user.opname.vouchers']);
        $OpnameVoucherRole->givePermissionTo(Permission::firstOrCreate(['name' => "opname-vouchers-create"]));
        $OpnameVoucherRole->givePermissionTo(Permission::firstOrCreate(['name' => "opname-vouchers-read"]));
        $OpnameVoucherRole->givePermissionTo(Permission::firstOrCreate(['name' => "opname-vouchers-update"]));
        $OpnameVoucherRole->givePermissionTo(Permission::firstOrCreate(['name' => "opname-vouchers-delete"]));
        Permission::firstOrCreate(['name' => "opname-vouchers-void"]);
        Permission::firstOrCreate(['name' => "opname-vouchers-validation"]);

        if ($admin = User::first()) {
            $admin->assignRole($OpnameStockRole);
            $admin->assignRole($OpnameVoucherRole);
            $admin->givePermissionTo('opname-stocks-void');
            $admin->givePermissionTo('opname-stocks-validation');
            $admin->givePermissionTo('opname-vouchers-void');
            $admin->givePermissionTo('opname-vouchers-validation');
        }
    }
}
