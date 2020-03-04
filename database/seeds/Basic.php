<?php

use Illuminate\Database\Seeder;
use App\Models\Auth\User;
use App\Models\Auth\Role;
use App\Models\Auth\Permission;
use App\Models\Reference\TypeItem;
use App\Models\Reference\CategoryItem;
use App\Models\Reference\Province;
use App\Models\Reference\Size;
use App\Models\Reference\Unit;
use App\Models\Reference\Shift;
use App\Models\Reference\TypeFault;

class Basic extends Seeder
{
	public function run()
    {

		$this->type_items();
        $this->category_items();
		$this->sizes();
		$this->units();
		$this->shifts();
        $this->faults();
        $this->provinces();
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

	public function shifts()
    {
        DB::table('shifts')->truncate();

		Shift::create(['id'=> 1,'name'=>'1','description'=>'The description for Shifting 1']);
		Shift::create(['id'=> 2,'name'=>'2','description'=>'The description for Shifting 2']);
		Shift::create(['id'=> 3,'name'=>'3','description'=>'The description for Shifting 3']);
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

		Unit::create(['id'=> 1,'code'=>strtoupper('Pcs'),'name'=>'Pcs']);
		Unit::create(['id'=> 2,'code'=>strtoupper('Kg'),'name'=>'KiloGram']);
		Unit::create(['id'=> 3,'code'=>strtoupper('dm'),'name'=>'Decimeter']);
		Unit::create(['id'=> 4,'code'=>strtoupper('brl'),'name'=>'Barel']);
		Unit::create(['id'=> 5,'code'=>strtoupper('ltr'),'name'=>'Liter']);
		Unit::create(['id'=> 6,'code'=>strtoupper('mtr'),'name'=>'Meter']);
		Unit::create(['id'=> 7,'code'=>strtoupper('ton'),'name'=>'Ton']);

    }

    public function provinces()
    {

        DB::table('provinces')->truncate();

		Province::create(['id'=> 1, 'name'=> 'Nanggroe Aceh Darussalam']);
		Province::create(['id'=> 2, 'name'=> 'Sumatera Utara']);
		Province::create(['id'=> 3, 'name'=> 'Sumatera Barat']);
		Province::create(['id'=> 4, 'name'=> 'Riau']);
		Province::create(['id'=> 5, 'name'=> 'Kepulauan Riau']);
		Province::create(['id'=> 6, 'name'=> 'Jambi']);
		Province::create(['id'=> 7, 'name'=> 'Sumatera Selatan']);
		Province::create(['id'=> 8, 'name'=> 'Bangka Belitung']);
		Province::create(['id'=> 9, 'name'=> 'Bengkulu']);
		Province::create(['id'=> 10, 'name'=> 'Lampung']);
		Province::create(['id'=> 11, 'name'=> 'DKI Jakarta']);
		Province::create(['id'=> 12, 'name'=> 'Jawa Barat']);
		Province::create(['id'=> 13, 'name'=> 'Banten']);
		Province::create(['id'=> 14, 'name'=> 'Jawa Tengah']);
		Province::create(['id'=> 15, 'name'=> 'D. I. Yogyakarta']);
		Province::create(['id'=> 16, 'name'=> 'Jawa Timur']);
		Province::create(['id'=> 17, 'name'=> 'Bali']);
		Province::create(['id'=> 18, 'name'=> 'Nusa Tenggara Barat']);
		Province::create(['id'=> 19, 'name'=> 'Nusa Tenggara Timur']);
		Province::create(['id'=> 20, 'name'=> 'Kalimantan Barat']);
		Province::create(['id'=> 21, 'name'=> 'Kalimantan Tengah']);
		Province::create(['id'=> 22, 'name'=> 'Kalimantan Selatan']);
		Province::create(['id'=> 23, 'name'=> 'Kalimantan Timur']);
		Province::create(['id'=> 24, 'name'=> 'Kalimantan Utara']);
		Province::create(['id'=> 25, 'name'=> 'Sulawesi Utara']);
		Province::create(['id'=> 26, 'name'=> 'Sulawesi Barat']);
		Province::create(['id'=> 27, 'name'=> 'Sulawesi Tengah']);
		Province::create(['id'=> 28, 'name'=> 'Sulawesi Tenggara']);
		Province::create(['id'=> 29, 'name'=> 'Sulawesi Selatan']);
		Province::create(['id'=> 30, 'name'=> 'Gorontalo']);
		Province::create(['id'=> 31, 'name'=> 'Maluku']);
		Province::create(['id'=> 32, 'name'=> 'Maluku Utara']);
		Province::create(['id'=> 33, 'name'=> 'Papua Barat']);
		Province::create(['id'=> 34, 'name'=> 'Papua']);

    }

	public function category_items()
    {
        DB::table('category_items')->truncate();

		CategoryItem::create(['id'=> 1,'name'=>'GENERAL','description'=>'The description for genaral']);
		CategoryItem::create(['id'=> 2,'name'=>'2-WHEEL','description'=>'The description for two-wheel vehicle']);
		CategoryItem::create(['id'=> 3,'name'=>'4-WHEEL','description'=>'The description for our-wheel vehicle']);
        CategoryItem::create(['id'=> 4,'name'=>'ELECTRONIC','description'=>'The description for Electronic']);

    }

	public function type_items()
    {
        DB::table('type_items')->truncate();

		TypeItem::create(['id'=> 1,'name'=>'Regular','description'=>'This is Reguler']);
		TypeItem::create(['id'=> 2,'name'=>'Non-Reguler','description'=>'This is Non-Reguler']);

	}

}
