<?php

use Illuminate\Database\Seeder;
use Faker\Factory;

use App\Models\Reference\Specification;
use App\Models\Reference\Brand;
use App\Models\Reference\Color;
use App\Models\Reference\Size;
use App\Models\Reference\Unit;
use App\Models\Reference\TypeItem;
use App\Models\Reference\CategoryItem;
use App\Models\Common\Item;
use App\Models\Income\Customer;
use App\Models\Factory\Production;

class TestingTableSeeder extends Seeder
{
	public function run()
    {
		$this->brands();
		$this->colors();
		$this->sizes();
		$this->units();
		$this->type_items();
		$this->category_items();
		$this->specifications();
		$this->customers();
		$this->items();
		$this->productions();
	}

	public function items()
	{
		DB::table('items')->truncate();

		// $faker = new \Faker\Generator();
		// $faker->addProvider(new \Faker\Provider\Fakecar($faker));
		$faker = Faker\Factory::create();

		$string = array ('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','V','X','Y','Z');
		$number = array (1,2,3,4,5,6,7,8,9,0);

		for ($i=0; $i < 10; $i++) { 
			$brand_id = rand(1,5);
			$customer_id = rand(1,5);
			$specification_id = rand(1,14);

			$c1 = Brand::find($brand_id); 						// print($c1->code);
			$c2 = Customer::find($customer_id);					// print($c2->code);
			$c3 = Specification::find($specification_id);		// print($c3->code);

			$code = $c1->code .'-'. $c2->code .'-'. $c3->code;

			Item::create([
				'id'=> $i+1, 
				'code'=> $code, 
				'brand_id' => $brand_id,
				'customer_id' => $customer_id,
				'specification_id' => $specification_id,
				'part_number'=> $faker->randomNumber(),
				'part_mtr'=> 'MTR-'. $faker->randomNumber(),
				'part_fg'=>  'FG-'.  $faker->randomNumber(),
			]);
			
		}
		
	}
	public function customers()
    {
		// $faker = new Faker\Generator();
		// $faker->addProvider(new Faker\Provider\id_ID\PhoneNumber($faker));
		// $faker->addProvider(new Faker\Provider\id_ID\Address($faker));
		$faker = Faker\Factory::create();

		DB::table('customers')->truncate();
		Customer::create(['id'=> 1,'code'=>'ASJ','name'=>'Alissan Sentral Jaya','email'=> $faker->email,'phone'=> $faker->phoneNumber]);
		Customer::create(['id'=> 2,'code'=>'HII','name'=>'Hipo Intern Indonesia','email'=> $faker->email,'phone'=> $faker->phoneNumber]);
		Customer::create(['id'=> 3,'code'=>'DKB','name'=>'Duangsa karya Bersama','email'=> $faker->email,'phone'=> $faker->phoneNumber]);
		Customer::create(['id'=> 4,'code'=>'BJB', 'name'=>'Bersama Jaya Baru','email'=> $faker->email,'phone'=> $faker->phoneNumber]);
		Customer::create(['id'=> 5,'code'=>'BJ', 'name'=>'Bersama Jaya','email'=> $faker->email,'phone'=> $faker->phoneNumber]);
		
	}

	public function productions()
    {
        DB::table('productions')->truncate();
		
		Production::create(['id'=> 1,'name'=>'CUTTING CR.4','description'=>'The description for Production 1']);
		Production::create(['id'=> 2,'name'=>'CUTTING CR.6','description'=>'The description for Production 2']);
		Production::create(['id'=> 3,'name'=>'POLES TYPE A','description'=>'The description for Production 3']);
		Production::create(['id'=> 4,'name'=>'POLES TYPE B','description'=>'The description for Production 4']);
		Production::create(['id'=> 5,'name'=>'POLES TYPE C','description'=>'The description for Production 5']);
		Production::create(['id'=> 6,'name'=>'CHROMING S28','description'=>'The description for Production 6']);
		Production::create(['id'=> 7,'name'=>'CHROMING K35','description'=>'The description for Production 7']);
	}

	public function specifications()
    {
		DB::table('specifications')->truncate();
		
		Specification::create(['id'=> 1,'code'=>'FD1423422','name'=>'Found Direct 1','color_id'=> 1,'times_spray_white'=> 23.8,'times_spray_red'=> 21]);
		Specification::create(['id'=> 2,'code'=>'CL1424323','name'=>'Calm LAMP 1','color_id'=> 2,'times_spray_white'=> 23,'times_spray_red'=> 21]);
		Specification::create(['id'=> 3,'code'=>'CB1234242','name'=>'Cyber Bound 1','color_id'=> 1,'times_spray_white'=> 29,'times_spray_red'=> 25.6]);
		Specification::create(['id'=> 4,'code'=>'FD2423424','name'=>'Found Direct 2','color_id'=> 1,'times_spray_white'=> 23.8,'times_spray_red'=> 21]);
		Specification::create(['id'=> 5,'code'=>'CL28674','name'=>'Calm LAMP 2','color_id'=> 2,'times_spray_white'=> 23,'times_spray_red'=> 21]);
		Specification::create(['id'=> 6,'code'=>'CB636452','name'=>'Cyber Bound 2','color_id'=> 1,'times_spray_white'=> 29,'times_spray_red'=> 25.6]);
		Specification::create(['id'=> 7,'code'=>'FD364556','name'=>'Found Direct 3','color_id'=> 1,'times_spray_white'=> 23.8,'times_spray_red'=> 21]);
		Specification::create(['id'=> 8,'code'=>'CL6456463','name'=>'Calm LAMP 3','color_id'=> 2,'times_spray_white'=> 23,'times_spray_red'=> 21]);
		Specification::create(['id'=> 9,'code'=>'CB634223','name'=>'Cyber Bound 3','color_id'=> 1,'times_spray_white'=> 29,'times_spray_red'=> 25.6]);
		Specification::create(['id'=>10,'code'=>'FD6456644','name'=>'Found Direct 4','color_id'=> 1,'times_spray_white'=> 23.8,'times_spray_red'=> 21]);
		Specification::create(['id'=>11,'code'=>'CL2324234','name'=>'Calm LAMP 4','color_id'=> 2,'times_spray_white'=> 23,'times_spray_red'=> 21]);
		Specification::create(['id'=>12,'code'=>'CB964454','name'=>'Cyber Bound 4','color_id'=> 1,'times_spray_white'=> 29,'times_spray_red'=> 25.6]);
		Specification::create(['id'=>13,'code'=>'FD5396572','name'=>'Found Direct 5','color_id'=> 1,'times_spray_white'=> 23.8,'times_spray_red'=> 21]);
		Specification::create(['id'=>14,'code'=>'CL5454575','name'=>'Calm LAMP 5','color_id'=> 2,'times_spray_white'=> 23,'times_spray_red'=> 21]);
		Specification::create(['id'=>15,'code'=>'CB5263654','name'=>'Cyber Bound 5','color_id'=> 1,'times_spray_white'=> 29,'times_spray_red'=> 25.6]);

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
	
    public function brands()
    {
        DB::table('brands')->truncate();
		
		Brand::create(['id'=> 1,'code'=>'TP','name'=>'Toyota Parts','description' =>'The description of Toyota Parts']);
		Brand::create(['id'=> 2,'code'=>'HP','name'=>'Honda Parts']);
		Brand::create(['id'=> 3,'code'=>'AM','name'=>'Astra Motor']);
		Brand::create(['id'=> 4,'code'=>'GP','name'=>'Genuins Parts']);
		Brand::create(['id'=> 5,'code'=>'SI','name'=>'SIPart']);
	}
	
    public function colors()
    {
        DB::table('colors')->truncate();
		
		Color::create(['id'=> 1,'name'=>'black','description'=>'Black Color']);
		Color::create(['id'=> 2,'name'=>'white']);
		Color::create(['id'=> 3,'name'=>'silver']);
		Color::create(['id'=> 5,'name'=>'yelow']);
		Color::create(['id'=> 6,'name'=>'red']);
		Color::create(['id'=> 7,'name'=>'black-1']);
		Color::create(['id'=> 8,'name'=>'black-2']);
		Color::create(['id'=> 9,'name'=>'black-3']);

	}

	public function sizes()
    {
        DB::table('sizes')->truncate();
		
		Size::create(['id'=> 1,'code'=>'XS','name'=>'X-Small']);
		Size::create(['id'=> 2,'code'=>'S','name'=>'Small']);
		Size::create(['id'=> 3,'code'=>'M','name'=>'Middle']);
		Size::create(['id'=> 4,'code'=>'L','name'=>'large']);
		Size::create(['id'=> 5,'code'=>'XL','name'=>'X-Large']);
		Size::create(['id'=> 6,'code'=>'XXL','name'=>'XX-Large']);

	}
	
	public function units()
    {
        DB::table('units')->truncate();
		
		Unit::create(['id'=> 1,'code'=>'PCS','name'=>'Packs']);
		Unit::create(['id'=> 2,'code'=>'KG','name'=>'Kilo gram']);
		Unit::create(['id'=> 3,'code'=>'TON','name'=>'Ton']);
		Unit::create(['id'=> 4,'code'=>'BRL','name'=>'Barel']);
		Unit::create(['id'=> 5,'code'=>'LTR','name'=>'Liter']);
		Unit::create(['id'=> 6,'code'=>'M','name'=>'Meter']);
		Unit::create(['id'=> 7,'code'=>'DM','name'=>'Deci Meter']);

    }
}
