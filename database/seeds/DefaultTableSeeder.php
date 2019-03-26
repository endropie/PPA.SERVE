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
		

		$crud = ['c'=>'create', 'r'=>'read', 'u'=>'edit', 'd'=>'delete'];
		$data = [
			// Auth
			'users' => ['c','r','u','d'],
			'roles' => ['c','r','u','d'],
			'permissions' => ['c','r','u','d'],
			'personal'=> ['r','u'],
			// Common 
			'items' => ['c','r','u','d'],
			// Factories
			'packing-items' => ['c','r','u','d'],
			'workin-productions' => ['c','r','u','d'],
			'work-orders' => ['c','r','u','d'],
			// Incomes
			'customers' => ['c','r','u','d'],
			'forecasts' => ['c','r','u','d'],
			'requests' 	=> ['c','r','u','d'],
			'deliveries'=> ['c','r','u','d'],
			'invoices' 	=> ['c','r','u','d'],
			// Expenses
			'vendors'	=> ['c','r','u','d'], 
			'purchases'	=> ['c','r','u','d'], 
			'bills'		=> ['c','r','u','d'],
			// Warehouse
			'incoming-goods' => ['c','r','u','d'],
		];

		$roles = [
			'user-auth'	=> ['users', 'roles', 'permissions'],
			'user-common'	=> ['items'],
			'user-factory'	=> ['packing-items', 'workin-productions', 'work-orders'],
			'user-income'	=> ['customers', 'forecasts', 'requests', 'deliveries', 'invoices'],
			'user-expense'	=> ['vendors', 'purchases', 'bills'],
			'user-warehouse'=> ['incoming-goods'],
		];

		
		$admin = User::create(['name' => 'admin', 'password' => Hash::make('admin'), 'email' => 'admin@mail.com']);
		foreach ($roles as $key => $value) {
			$name = str_replace('-','.', $key);
			$user = User::create(['name' => $name, 'password' => Hash::make($name), 'email' => $name .'@mail.com']);
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
