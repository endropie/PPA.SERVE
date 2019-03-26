<?php

use Illuminate\Database\Seeder;
use Faker\Factory;

use App\Models\Common\Item;
use App\Models\Reference\Specification;
use App\Models\Reference\Brand;
use App\Models\Reference\Color;
use App\Models\Reference\Size;
use App\Models\Reference\Unit;
use App\Models\Income\Customer;
use App\Models\Reference\Line;
use App\Models\Reference\Shift;
use App\Models\Reference\Fault;
use App\Models\Reference\TypeFault;
use App\Models\Reference\Operator;
use App\Models\Reference\Vehicle;
use App\Models\Reference\Rit;

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
		$this->faults();
		$this->vehicles();
		$this->operators();
		$this->specifications();
		$this->customers();
		$this->items();
	}

	public function items()
	{
		DB::table('items')->truncate();
		DB::table('item_prelines')->truncate();
		DB::table('item_units')->truncate();

		// $faker = new \Faker\Generator();
		// $faker->addProvider(new \Faker\Provider\Fakecar($faker));
		$faker = Faker\Factory::create();

		$strings = array ("AS", "BU", "GT", "HD", "HJL", "QQ", "WUG", "US", "KD", "EA", "AF");
		$hangers = array("20", "20", "50", "24", "20");

		for ($k=1; $k <= 5; $k++) {
		 for ($j=1; $j <= 5; $j++) { 
		  
		  if(rand(1,10)  > 5)
		  for ($i=1; $i <= 15; $i++) { 
			
			if( rand(1,10) > 7) {
				$specification_id = $i;
				$brand_id = $j;
				$customer_id = $k;

				$rand_hanger = array_rand($hangers, 2);
				
				$c1 = Customer::find($customer_id);
				$c2 = Brand::find($brand_id);
				$c3 = Specification::find($specification_id);

				// print($k .'-'. $j .'-'. $i ."\n");
				$code = $c1->code .'-'. $c2->code .'-'. $c3->code;

				$name = $strings[array_rand($strings, 2)[0]] . '-'. $faker->randomNumber(3);
				$item = Item::create([
					'id' => null, 
					'code' => $code, 
					'customer_id' 	=> $customer_id,
					'brand_id' 		=> $brand_id,
					'specification_id' => $specification_id,
					'part_number'	=> $faker->randomNumber(8),
					'part_name' 	=> $name,
					'part_alias' 	=> $faker->randomNumber(1) < 5 ? $name .'-'. $faker->randomNumber(2) : null,
					'number_hanger' => $hangers[$rand_hanger[0]],
					'unit_id' => 1,
					'type_item_id' => 1,
					'category_item_id' => 1,
					'size_id' => rand(1,5),
					'price' => rand(14,60) * 1000
				]);
				
				// Generate Prelines..
				for ($x=0; $x < rand(1,3); $x++) {
					$item->item_prelines()->create(['line_id' => rand(1,23)]);
				}
				// Generate Prelines..
				if($faker->randomNumber(1) < 7) {
					$item->item_units()->create(['unit_id' => 2, 'rate' => rand(0,2) + (1 / rand(2,4))]);
				}
			}
		  }
		 }
		}
		
	}
	public function customers()
    {
		$faker = Faker\Factory::create();
		$orders = ['NONE', 'BASEON', 'ACCUMULATE'];
		$deliveries = ['SEPARATE', 'JOIN', 'DETAIL'];
		$invoices = ['SEPARATE', 'JOIN', 'DETAIL', 'UNIT_DETAIL'];

		DB::table('customers')->truncate();

		Customer::create([
			'id'=> 1,'code'=>'ASJ','name'=>'Allisan Sentral Jaya','email'=> $faker->email,'phone'=> $faker->phoneNumber, 'address'=> $faker->address, 'province_id'=> rand(11,13), 'zipcode'=> rand(11111,79999),
			'order_mode' 	=> $orders[0], 'delivery_mode'	=> $deliveries[array_rand($deliveries)], 'invoice_mode' 	=> $invoices[array_rand($invoices)],
		]);
		
		Customer::create([
			'id'=> 2,'code'=>'HII','name'=>'Hipo Intern Indonesia','email'=> $faker->email,'phone'=> $faker->phoneNumber, 'address'=> $faker->address, 'province_id'=> rand(11,13), 'zipcode'=> rand(11111,79999),
			'order_mode' 	=> $orders[array_rand($orders)], 'delivery_mode'	=> $deliveries[array_rand($deliveries)], 'invoice_mode' 	=> $invoices[array_rand($invoices)],
		]);
		Customer::create([
			'id'=> 3,'code'=>'DKB','name'=>'Duangsa karya Bersama','email'=> $faker->email,'phone'=> $faker->phoneNumber, 'address'=> $faker->address, 'province_id'=> rand(11,13), 'zipcode'=> rand(11111,79999),
			'order_mode' 	=> $orders[array_rand($orders)], 'delivery_mode'	=> $deliveries[array_rand($deliveries)], 'invoice_mode' 	=> $invoices[array_rand($invoices)],
		]);
		Customer::create([
			'id'=> 4,'code'=>'BJB', 'name'=>'Bersama Jaya Baru','email'=> $faker->email,'phone'=> $faker->phoneNumber, 'address'=> $faker->address, 'province_id'=> rand(11,13), 'zipcode'=> rand(11111,79999),
			'order_mode' 	=> $orders[array_rand($orders)], 'delivery_mode'	=> $deliveries[array_rand($deliveries)], 'invoice_mode' 	=> $invoices[array_rand($invoices)],
		]);
		Customer::create([
			'id'=> 5,'code'=>'ZM', 'name'=>'Zen Motor','email'=> $faker->email,'phone'=> $faker->phoneNumber, 'address'=> $faker->address, 'province_id'=> rand(11,13), 'zipcode'=> rand(11111,79999),
			'order_mode' 	=> $orders[array_rand($orders)], 'delivery_mode'	=> $deliveries[array_rand($deliveries)], 'invoice_mode' 	=> $invoices[array_rand($invoices)],
		]);
		
	}

	public function faults()
    {
		DB::table('faults')->truncate();
		DB::table('type_faults')->truncate();
		
		$coating = TypeFault::create(['id'=> 1,'name'=>'ED Coating','description'=>'This Description is Not Good Coating']);
		$coating->faults()->create(['name'=>'Others']);
		$coating->faults()->create(['name'=>'Kabut']);
		$coating->faults()->create(['name'=>'Buram']);
		$coating->faults()->create(['name'=>'Scratch']);
		$coating->faults()->create(['name'=>'Jamur']);
		$coating->faults()->create(['name'=>'No paint']);
		$coating->faults()->create(['name'=>'Kasar']);
		$coating->faults()->create(['name'=>'Nempel Jig']);
		$coating->faults()->create(['name'=>'Water over']);

		$plating = TypeFault::create(['id'=> 2,'name'=>'ZN Plating','description'=>'This Description is Not Good Plating']);
		$coating->faults()->create(['name'=>'Others']);
		$plating->faults()->create(['name'=>'Kotor']);
		$plating->faults()->create(['name'=>'keropos']);
		$plating->faults()->create(['name'=>'karat']);
		$plating->faults()->create(['name'=>'Jamur']);
	}

	public function operators()
    {
        DB::table('operators')->truncate();
		$faker = Faker\Factory::create();

		Operator::create(['id'=> 1,'name'=> $faker->name(),'phone'=> $faker->phoneNumber]);
		Operator::create(['id'=> 2,'name'=> $faker->name(),'phone'=> $faker->phoneNumber]);
		Operator::create(['id'=> 3,'name'=> $faker->name(),'phone'=> $faker->phoneNumber]);
		Operator::create(['id'=> 4,'name'=> $faker->name(),'phone'=> $faker->phoneNumber]);
		Operator::create(['id'=> 5,'name'=> $faker->name(),'phone'=> $faker->phoneNumber]);
	}

	public function vehicles()
    {
        DB::table('vehicles')->truncate();
		$faker = Faker\Factory::create();

		Vehicle::create(['name'=> 'B '. rand(1000,9999) . ' SCD']);
		Vehicle::create(['name'=> 'B '. rand(1000,9999) . ' FRT']);
		Vehicle::create(['name'=> 'B '. rand(1000,9999) . ' VFR']);
		Vehicle::create(['name'=> 'B '. rand(1000,9999) . ' VV']);
		Vehicle::create(['name'=> 'B '. rand(1000,9999) . ' YUT']);
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
		$faker = Faker\Factory::create();

		Specification::create(['id'=> 1,'code'=>'FD'.$faker->randomNumber(rand(3,6)),'name'=>'Found Direct 1','color_id'=> 1,'times_spray_white'=> 23.8,'times_spray_red'=> 21]);
		Specification::create(['id'=> 2,'code'=>'CL'.$faker->randomNumber(rand(3,6)),'name'=>'Calm LAMP 1','color_id'=> 2,'times_spray_white'=> 23,'times_spray_red'=> 21]);
		Specification::create(['id'=> 3,'code'=>'CB'.$faker->randomNumber(rand(3,6)),'name'=>'Cyber Bound 1','color_id'=> 1,'times_spray_white'=> 29,'times_spray_red'=> 25.6]);
		Specification::create(['id'=> 4,'code'=>'YM'.$faker->randomNumber(rand(3,6)),'name'=>'Found Direct 2','color_id'=> 1,'times_spray_white'=> 23.8,'times_spray_red'=> 21]);
		Specification::create(['id'=> 5,'code'=>'NM'.$faker->randomNumber(rand(3,6)),'name'=>'Calm LAMP 2','color_id'=> 2,'times_spray_white'=> 23,'times_spray_red'=> 21]);
		Specification::create(['id'=> 6,'code'=>'CB'.$faker->randomNumber(rand(3,6)),'name'=>'Cyber Bound 2','color_id'=> 1,'times_spray_white'=> 29,'times_spray_red'=> 25.6]);
		Specification::create(['id'=> 7,'code'=>'PQ'.$faker->randomNumber(rand(3,6)),'name'=>'Found Direct 3','color_id'=> 1,'times_spray_white'=> 23.8,'times_spray_red'=> 21]);
		Specification::create(['id'=> 8,'code'=>'WR'.$faker->randomNumber(rand(3,6)),'name'=>'Calm LAMP 3','color_id'=> 2,'times_spray_white'=> 23,'times_spray_red'=> 21]);
		Specification::create(['id'=> 9,'code'=>'CB'.$faker->randomNumber(rand(3,6)),'name'=>'Cyber Bound 3','color_id'=> 1,'times_spray_white'=> 29,'times_spray_red'=> 25.6]);
		Specification::create(['id'=>10,'code'=>'FD'.$faker->randomNumber(rand(3,6)),'name'=>'Found Direct 4','color_id'=> 1,'times_spray_white'=> 23.8,'times_spray_red'=> 21]);
		Specification::create(['id'=>11,'code'=>'SD'.$faker->randomNumber(rand(3,6)),'name'=>'Calm LAMP 4','color_id'=> 2,'times_spray_white'=> 23,'times_spray_red'=> 21]);
		Specification::create(['id'=>12,'code'=>'CB'.$faker->randomNumber(rand(3,6)),'name'=>'Cyber Bound 4','color_id'=> 1,'times_spray_white'=> 29,'times_spray_red'=> 25.6]);
		Specification::create(['id'=>13,'code'=>'FD'.$faker->randomNumber(rand(3,6)),'name'=>'Found Direct 5','color_id'=> 1,'times_spray_white'=> 23.8,'times_spray_red'=> 21]);
		Specification::create(['id'=>14,'code'=>'RE'.$faker->randomNumber(rand(3,6)),'name'=>'Calm LAMP 5','color_id'=> 2,'times_spray_white'=> 23,'times_spray_red'=> 21]);
		Specification::create(['id'=>15,'code'=>'CB'.$faker->randomNumber(rand(3,6)),'name'=>'Cyber Bound 5','color_id'=> 1,'times_spray_white'=> 29,'times_spray_red'=> 25.6]);

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
		
		Unit::create(['id'=> 1,'code'=>'Pcs','name'=>'Pcs']);
		Unit::create(['id'=> 2,'code'=>'Kg','name'=>'KiloGram']);
		Unit::create(['id'=> 3,'code'=>'dm','name'=>'Decimeter']);
		Unit::create(['id'=> 4,'code'=>'brl','name'=>'Barel']);
		Unit::create(['id'=> 5,'code'=>'ltr','name'=>'Liter']);
		Unit::create(['id'=> 6,'code'=>'mtr','name'=>'Meter']);
		Unit::create(['id'=> 7,'code'=>'ton','name'=>'Ton']);

    }
}
