<?php

use Illuminate\Database\Seeder;
use Faker\Factory;

use App\Models\Reference\TypeItem;
use App\Models\Reference\CategoryItem;
use App\Models\Reference\TypeWorktime;

class DefaultTableSeeder extends Seeder
{
	public function run()
    {
		$this->type_items();
		$this->category_items();
		$this->type_worktimes();
	}

	public function type_worktimes()
    {
        DB::table('type_worktimes')->truncate();
		
		TypeWorktime::create(['id'=> 1,'name'=>'reguler','description'=>'The description for Reguler']);
		TypeWorktime::create(['id'=> 2,'name'=> 'overtime','description'=>'The description for OverTime']);
	}

	public function category_items()
    {
        DB::table('category_items')->truncate();
		
		CategoryItem::create(['id'=> 1,'name'=>'2-wheel vehicle','description'=>'The description for two-wheel vehicle']);
		CategoryItem::create(['id'=> 2,'name'=>'3-wheel vehicle','description'=>'The description for three-wheel vehicle']);
		CategoryItem::create(['id'=> 3,'name'=>'4-wheel vehicle','description'=>'The description for our-wheel vehicle']);
	}
	
	public function type_items()
    {
        DB::table('type_items')->truncate();
		
		TypeItem::create(['id'=> 1,'name'=>'Regular','description'=>'This is Reguler']);
		TypeItem::create(['id'=> 2,'name'=>'Non-Reguler','description'=>'This is Non-Reguler']);

	}
}
