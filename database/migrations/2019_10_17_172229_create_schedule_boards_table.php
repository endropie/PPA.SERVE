<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Auth\User;
use App\Models\Auth\Role;
use App\Models\Auth\Permission;
use Illuminate\Support\Facades\DB;

class CreateScheduleBoardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::create('schedule_boards', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('number');
            $table->integer('customer_id');
            $table->integer('vehicle_id');
            $table->integer('operator_id')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('status')->default('OPEN');

            $table->dateTime('departed_at')->nullable();
            $table->dateTime('arrived_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('is_scheduled')->default(0)->after('description');
        });

        // Create Setting
        $this->setSetting();

        // Create Role & permission
        $this->setPermiss();

        DB::commit();
    }

    protected function setSetting () {
        $setting = setting();
        $setting->set([
            'schedule_board.number_prefix'     => 'SCH',
            'schedule_board.number_interval'   => '{Y-m}',
            'schedule_board.number_digit'      => '5',
        ]);
        $setting->save();
    }

    protected function setPermiss () {
        $newRole = Role::create(['name' => 'user.schedule.boards']);
        $newRole->givePermissionTo(Permission::firstOrCreate(['name' => "schedule-boards-create"]));
        $newRole->givePermissionTo(Permission::firstOrCreate(['name' => "schedule-boards-read"]));
        $newRole->givePermissionTo(Permission::firstOrCreate(['name' => "schedule-boards-update"]));
        $newRole->givePermissionTo(Permission::firstOrCreate(['name' => "schedule-boards-delete"]));
        $newRole->givePermissionTo(Permission::firstOrCreate(['name' => "schedule-boards-void"]));

        if ($admin = User::first()) {
            $admin->assignRole($newRole);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();

        Schema::dropIfExists('schedule_boards');

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['is_scheduled']);
        });
        DB::commit();
    }
}
