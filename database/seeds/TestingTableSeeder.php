<?php

use Illuminate\Database\Seeder;
use Faker\Factory;

use App\Models\Reference\Specification;
use App\Models\Reference\Brand;
use App\Models\Reference\Color;
use App\Models\Reference\Size;
use App\Models\Reference\Unit;
use App\Models\Common\Item;
use App\Models\Income\Customer;
use App\Models\Reference\Line;
use App\Models\Reference\Shift;

class TestingTableSeeder extends Seeder
{
	public function run()
    {
		$this->brands();
		$this->colors();
		$this->sizes();
		$this->units();
		$this->lines();
		$this->shifts();
		$this->specifications();
		$this->customers();
		$this->items();
	}

	public function items()
	{
		DB::table('items')->truncate();

		// $faker = new \Faker\Generator();
		// $faker->addProvider(new \Faker\Provider\Fakecar($faker));
		$faker = Faker\Factory::create();

		$strings = array ("AS", "BU", "GT", "HD", "HJL", "QQ", "WUG", "US", "KD", "EA", "AF");
		$hangers = array("20", "20", "50", "24", "20");

		for ($i=0; $i < 15; $i++) { 
			$brand_id = rand(1,5);
			$customer_id = rand(1,5);
			$specification_id = $i+1;

			$rand_hanger = array_rand($hangers, 2);
			
			$c1 = Brand::find($brand_id); 						// print($c1->code);
			$c2 = Customer::find($customer_id);					// print($c2->code);
			$c3 = Specification::find($specification_id);		// print($c3->code);

			$code = $c1->code .'-'. $c2->code .'-'. $c3->code;

			$name = $strings[array_rand($strings, 2)[0]] . '-'. $faker->randomNumber(5);
			$item = Item::create([
				'id' => $i+1, 
				'code' => $code, 
				'brand_id' 		=> $brand_id,
				'customer_id' 	=> $customer_id,
				'specification_id' => $specification_id,
				'part_number'	=> $faker->randomNumber(8),
				'part_name' 	=> $name,
				'part_alias' 	=> $faker->randomNumber(1) < 5 ? $name .'-'. $faker->randomNumber(2) : null,
				'number_hanger' => $hangers[$rand_hanger[0]],
				'unit_id' => 1,
				'type_item_id' => 1,
				'category_item_id' => 1,
				'size_id' => rand(1,5)
			]);
			
			for ($j=0; $j < rand(2,5); $j++) {
				$item->item_prelines()->create(['line_id' => rand(1,23)]);
			}
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

	public function shifts()
    {
        DB::table('shifts')->truncate();
		
		Shift::create(['id'=> 1,'name'=>'1-A','description'=>'The description for Shifting 1A']);
		Shift::create(['id'=> 2,'name'=>'1-B','description'=>'The description for Shifting 1B']);
		Shift::create(['id'=> 3,'name'=>'1-C','description'=>'The description for Shifting 1C']);
		Shift::create(['id'=> 4,'name'=>'2','description'=>'The description for Shifting 2']);
		Shift::create(['id'=> 5,'name'=>'3','description'=>'The description for Shifting 3']);
	}

	public function lines()
    {
        DB::table('lines')->truncate();
		
		Line::create(['id'=> 1,'name'=>'ED Coating-1 (Epoxy)','description'=>'The description for Line 1']);
		Line::create(['id'=> 2,'name'=>'Removing 1','description'=>'The description for Line 2']);
		Line::create(['id'=> 3,'name'=>'Dipping','description'=>'The description for Line 3']);
		Line::create(['id'=> 4,'name'=>'Chromated Manual K','description'=>'The description for Line 4']);
		Line::create(['id'=> 5,'name'=>'Zn (Alkali) Rack-1','description'=>'The description for Line 5']);
		Line::create(['id'=> 6,'name'=>'Greend Chromate','description'=>'The description for Line 6']);
		Line::create(['id'=> 7,'name'=>'Spray Coating','description'=>'The description for Line 7']);
		Line::create(['id'=> 8,'name'=>'Treathment Degreasing','description'=>'The description for Line 8']);
		Line::create(['id'=> 9,'name'=>'Zn Iron','description'=>'The description for Line 9']);
		Line::create(['id'=> 10,'name'=>'Chromated Automatic','description'=>'The description for Line 10']);
		Line::create(['id'=> 11,'name'=>'Zn (Acid) Barrel','description'=>'The description for Line 11']);
		Line::create(['id'=> 12,'name'=>'Chromated Manual Barrel','description'=>'The description for Line 12']);
		Line::create(['id'=> 13,'name'=>'ZN (Alkali) Rack-2','description'=>'The description for Line 13']);
		Line::create(['id'=> 14,'name'=>'ED Coating-3 (Acrylic)','description'=>'The description for Line 14']);
		Line::create(['id'=> 15,'name'=>'ED Coating-4 (Acrylic)','description'=>'The description for Line 15']);
		Line::create(['id'=> 16,'name'=>'Mangan Phospat','description'=>'The description for Line 16']);
		Line::create(['id'=> 17,'name'=>'Mini Barrel','description'=>'The description for Line 17']);
		Line::create(['id'=> 18,'name'=>'Silver Plating','description'=>'The description for Line 18']);
		Line::create(['id'=> 19,'name'=>'Plasticol Coating','description'=>'The description for Line 19']);
		Line::create(['id'=> 20,'name'=>'Repair','description'=>'The description for Line 20']);
		Line::create(['id'=> 21,'name'=>'Removing 2','description'=>'The description for Line 21']);
		Line::create(['id'=> 22,'name'=>'Touchup','description'=>'The description for Line 22']);
		Line::create(['id'=> 23,'name'=>'Gudang','description'=>'The description for Line 23']);
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
