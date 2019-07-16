<?php

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\Auth\User;
use App\Models\Auth\Role;
use App\Models\Auth\Permission;
use App\Models\Reference\TypeItem;
use App\Models\Reference\CategoryItem;
use App\Models\Reference\TypeWorktime;

class DefaultTableSeeder extends Seeder
{
	public function run()
    {
		// Create generate Passport Client: personal
		// \Artisan::call('passport:client --personal');
		// \Artisan::call('passport:client --password');
		if(app()->runningInConsole()) {
			\Artisan::call('passport:install');
		}

		// \Artisan::call('passport:install');

		$this->auth();
		$this->type_items();
		$this->category_items();
	}

	public function category_items()
    {
        DB::table('category_items')->truncate();
		
		CategoryItem::create(['id'=> 1,'name'=>'2-wheel vehicle','description'=>'The description for two-wheel vehicle']);
		CategoryItem::create(['id'=> 3,'name'=>'4-wheel vehicle','description'=>'The description for our-wheel vehicle']);
	}
	
	public function type_items()
    {
        DB::table('type_items')->truncate();
		
		TypeItem::create(['id'=> 1,'name'=>'Regular','description'=>'This is Reguler']);
		TypeItem::create(['id'=> 2,'name'=>'Non-Reguler','description'=>'This is Non-Reguler']);

	}

	public function auth() {
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		DB::table('auth_model_has_permissions')->truncate();
		DB::table('auth_model_has_roles')->truncate();
		DB::table('auth_role_has_permissions')->truncate();
		DB::table('auth_permissions')->truncate();
		DB::table('auth_roles')->truncate();
		DB::table('users')->truncate();

		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
		

		$crud = ['c'=>'create', 'r'=>'read', 'u'=>'update', 'd'=>'delete'];
		$data = [
			// Auth
			'users' => ['c','r','u','d'],
			'roles' => ['c','r','u','d'],
			'permissions' => ['c','r','u','d'],
			'profile'=> ['r','u'],
			// Common 
			'items' => ['c','r','u','d'],
			// Factories
			'packings' => ['c','r','u','d'],
			'workin-productions' => ['c','r','u','d'],
			'work-orders' => ['c','r','u','d'],
			// Incomes
			'customers' => ['c','r','u','d'],
			'forecasts' => ['c','r','u','d'],
			'request-orders' => ['c','r','u','d'],
			'pre-deliveries' => ['c','r','u','d'],
			'ship-deliveries' => ['c','r','u','d'],
			'ship-delivery-items' => ['c','r','u','d'],
			'delivery-orders' => ['c','r','u','d'],
			'invoices' 	=> ['c','r','u','d'],
			// Expenses
			'vendors'	=> ['c','r','u','d'], 
			'purchases'	=> ['c','r','u','d'], 
			'bills'		=> ['c','r','u','d'],
			// Warehouse
			'incoming-goods' => ['c','r','u','d'],
			// Reference
			'brands'		=> ['c','r','u','d'],
			'colors' => ['c','r','u','d'],
			'faults'	=> ['c','r','u','d'],
			'lines'	=> ['c','r','u','d'],
			'shifts'	=> ['c','r','u','d'],
			'sizes'	=> ['c','r','u','d'],
			'specifications' => ['c','r','u','d'],
			'specification-details'	=> ['c','r','u','d'],
			'type-faults'	=> ['c','r','u','d'],
			'type-items'	=> ['c','r','u','d'],
			'units'	=> ['c','r','u','d'],
			'vehicles'	=> ['c','r','u','d'],
		];

		$roles = [
			'user-auth'	=> ['users', 'roles', 'permissions', 'profile'],
			'user-common'	=> ['items'],
			'user-factory'	=> ['packings', 'workin-productions', 'work-orders'],
			'user-income'	=> ['customers', 'forecasts', 'request-orders', 'pre-deliveries', 'ship-deliveries', 'ship-delivery-items', 'delivery-orders', 'invoices' ],
			'user-expense'	=> ['vendors', 'purchases', 'bills'],
			'user-warehouse'=> ['incoming-goods'],
			'user-reference'=> [
				'brands', 'colors', 'faults', 'lines', 'shifts', 'sizes', 
				'specifications', 'specification-details', 
				'type-faults', 'type-items', 'units', 'vehicles'
			]
		];

		
		$admin = User::create(['name' => 'admin', 'password' => Hash::make('admin'.'ppa'), 'email' => 'admin@ppa.com']);
		foreach ($roles as $key => $value) {
			$name = str_replace('-','.', $key); 						
			$pass = Hash::make(str_replace('user-','', $key).'ppa');
			// Ex: username: user.reference@ppa.com password: referenceppa

			$user = User::create(['name' => $name, 'password' => $pass, 'email' => $name .'@ppa.com']);
			$role = Role::create(['name' => $key]);
			$user->assignRole($key);
			$admin->assignRole($key);
		}

		foreach ($data as $key => $actions) {
			foreach ($actions as $action) {
				$label = $crud[$action] ?? $action;
				$permission = Permission::create(['name' => $key .'-'. $label]);
				foreach ($roles as $rcode => $values) {
					if (in_array($key, $values)) {
						if($role = Role::where('name',$rcode)->first()) {
							$role->givePermissionTo($permission);
						}
					}
				}
			}
		}
	}
}
