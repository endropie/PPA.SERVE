<?php

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class RolePermission extends Seeder
{
	public function run()
    {
        DB::beginTransaction();

		$crud = ['c'=>'create', 'r'=>'read', 'u'=>'update', 'd'=>'delete'];
		$data = [
			// Auth
			'users' => ['c','r','u','d'],
			'roles' => ['c','r','u','d'],
            'permissions' => ['c','r','u','d'],
			// Common
			'customers' => ['c','r','u','d','push'],
			'items' => ['c','r','u','d','price','sample','reference','push'],
			'employees' => ['c','r','u','d','reference'],
			// Factories
			'packings' => ['c','r','u','d','close','void'],
			'work-orders' => ['c','r','u','d','close','revision','void','validation'],
			'work-productions' => ['c','r','u','d','close','void'],
			'work-process' => ['r','confirm'],
			// Incomes
			'forecasts' => ['c','r','u','d','close','void'],
			'request-orders' => ['c','r','u','d','close','revision','void', 'push'],
            'acc-invoices' => ['c','r','u','d','confirm', 'reopen'],
			// Warehouses
            'opname-stocks' => ['c','r','u','d','validation','revision','void'],
            'opname-vouchers' => ['c','r','u','d','validation','revision','void'],
            // Deliveries
			'incoming-goods' => ['c','r','u','d','validation','revision','void'],
            'outgoing-verifications' => ['c','r','u','d'],
			'outgoing-goods' => ['c','r','d','void'],
			'pre-deliveries' => ['c','r','u','d','close','revision','void'],
            'sj-delivery-orders' => ['c','r','u','d','confirm','revision','void'],
            'sj-delivery-internals' => ['c','r','u','d','confirm','revision','void'],
            'delivery-internals' => ['c','r','u','d','confirm','revision','void'],
			'delivery-tasks' => ['c','r','u','d','void'],
			'delivery-verifies' => ['c','r','d','void'],
            'delivery-loads' => ['c','r','void'],
            'delivery-checkouts' => ['c','r','void'],
			'deportation-goods' => ['c','r','u','d','validation','revision','void'],
			'schedule-boards' => ['c','r','u','d','void'],
			// Reference
			'brands'		=> ['c','r','u','d'],
			'colors' => ['c','r','u','d'],
			'faults'	=> ['c','r','u','d'],
			'lines'	=> ['c','r','u','d'],
			'shifts'	=> ['c','r','u','d'],
			'sizes'	=> ['c','r','u','d'],
			'specifications' => ['c','r','u','d'],
			'type-faults'	=> ['c','r','u','d'],
			'type-items'	=> ['c','r','u','d'],
			'category-items'	=> ['c','r','u','d'],
            'units'	=> ['c','r','u','d'],
            'departments' => ['c','r','u','d'],
            'positions' => ['c','r','u','d'],
			'vehicles'	=> ['c','r','u','d'],
		];

		$roles = [
			'auth'	    => ['users', 'roles', 'permissions'],
			'common'    => ['items', 'employees'],
            'marketing' => ['customers', 'forecasts', 'request-orders' ],

            'invoice.collect' => ['acc-invoices'],

            'work.order' => ['work-orders'],
            'work.production' => ['work-productions'],
            'work.process' => ['work-process'],
            'packing' => ['packings'],

            'outgoing.verify' => ['outgoing-verifications'],
            'outgoing.good' => ['outgoing-goods'],
            'sj.delivery' => ['sj-delivery-orders'],
            'sj.internal' => ['sj-delivery-internals'],
            'pre.delivery' => ['pre-deliveries'],
            'delivery.internal' => ['delivery-internals'],
            'delivery.task' => ['delivery-tasks'],
            'delivery.verify' => ['delivery-verifies'],
            'delivery.load' => ['delivery-loads'],
            'delivery.checkout' => ['delivery-checkouts'],

            'incoming.good' => ['incoming-goods'],
            'opname.voucher' => ['opname-vouchers'],
            'opname.stock' => ['opname-stocks'],
            'deportation.good' => ['deportation-goods'],

			'reference' => [
				'brands', 'colors', 'faults', 'lines', 'shifts', 'sizes',
				'specifications', 'type-faults',
                'category-items', 'type-items', 'units',
                'departments', 'positions', 'vehicles'
			]
		];


        $profileRole = Role::firstOrCreate(['name' => 'profile']);
        $profileRole->givePermissionTo(Permission::firstOrCreate(['name' => "profile"]));

        $settingRole = Role::firstOrCreate(['name' => 'setting']);
        $settingRole->givePermissionTo(Permission::firstOrCreate(['name' => "setting"]));

		$admin = User::firstOrCreate(['email' => 'admin@ppa.com'],['name' => 'admin', 'password' => Hash::make('admin'.'ppa')]);

        $admin->assignRole($profileRole);
        $admin->assignRole($settingRole);

        foreach ($roles as $key => $value) {
			$name = ucfirst($key);
			$pass = Hash::make($key.'ppa');
			// Ex: username: user.reference@ppa.com password: referenceppa

            $user = User::firstOrCreate(['email' => strtolower($name .'@ppa.com')], ['name' => $name, 'password' => $pass]);
            $user->assignRole($profileRole->name);

            $label = "user.$key";
            $role = Role::firstOrCreate(['name' => $label]);
			$user->assignRole($label);
            $admin->assignRole($label);
		}

		foreach ($data as $key => $actions) {
			foreach ($actions as $action) {
				$label = $crud[$action] ?? $action;
				$permission = Permission::firstOrCreate(['name' => "$key-$label"]);
				foreach ($roles as $rcode => $values) {
					if (in_array($key, $values)) {
						if($role = Role::where('name',"user.$rcode")->first()) {
                            if(!empty($crud[$action])) $role->givePermissionTo($permission);
                            else {
                                $admin->givePermissionTo($permission);
                            }
                        }
					}
				}
			}
        }

        DB::commit();
	}
}
