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
		$coating = TypeFault::updateOrCreate(['id'=> 1], ['name'=>'ED Coating','description'=>'This Description is Not Good Coating']);
		$coating->faults()->firstOrCreate(['name'=>'Others']);
		$coating->faults()->firstOrCreate(['name'=>'Kabut']);
		$coating->faults()->firstOrCreate(['name'=>'Buram']);
		$coating->faults()->firstOrCreate(['name'=>'Scratch']);
		$coating->faults()->firstOrCreate(['name'=>'Jamur']);
		$coating->faults()->firstOrCreate(['name'=>'No paint']);
		$coating->faults()->firstOrCreate(['name'=>'Kasar']);
		$coating->faults()->firstOrCreate(['name'=>'Nempel Jig']);
		$coating->faults()->firstOrCreate(['name'=>'Water over']);

		$plating = TypeFault::updateOrCreate(['id'=> 2], ['name'=>'ZN Plating','description'=>'This Description is Not Good Plating']);
		$coating->faults()->firstOrCreate(['name'=>'Others']);
		$plating->faults()->firstOrCreate(['name'=>'Kotor']);
		$plating->faults()->firstOrCreate(['name'=>'keropos']);
		$plating->faults()->firstOrCreate(['name'=>'karat']);
		$plating->faults()->firstOrCreate(['name'=>'Jamur']);
	}

	public function shifts()
    {
		Shift::updateOrCreate(['id'=> 1],['name'=>'1','description'=>'The description for Shifting 1']);
		Shift::updateOrCreate(['id'=> 2],['name'=>'2','description'=>'The description for Shifting 2']);
		Shift::updateOrCreate(['id'=> 3],['name'=>'3','description'=>'The description for Shifting 3']);
	}

	public function sizes()
    {
		Size::updateOrCreate(['id'=> 1], ['code'=>'XS','name'=>'X-Small']);
		Size::updateOrCreate(['id'=> 2], ['code'=>'S','name'=>'Small']);
		Size::updateOrCreate(['id'=> 3], ['code'=>'M','name'=>'Middle']);
		Size::updateOrCreate(['id'=> 4], ['code'=>'L','name'=>'large']);
		Size::updateOrCreate(['id'=> 5], ['code'=>'XL','name'=>'X-Large']);
		Size::updateOrCreate(['id'=> 6], ['code'=>'XXL','name'=>'XX-Large']);

	}

	public function units()
    {
		Unit::updateOrCreate(['id'=> 1], ['code'=>strtoupper('Pcs'),'name'=>'Pcs']);
		Unit::updateOrCreate(['id'=> 2], ['code'=>strtoupper('Kg'),'name'=>'Kg']);
		Unit::updateOrCreate(['id'=> 3], ['code'=>strtoupper('dm'),'name'=>'dM']);
		Unit::updateOrCreate(['id'=> 4], ['code'=>strtoupper('brl'),'name'=>'Barel']);
		Unit::updateOrCreate(['id'=> 5], ['code'=>strtoupper('ltr'),'name'=>'Liter']);
		Unit::updateOrCreate(['id'=> 6], ['code'=>strtoupper('mtr'),'name'=>'Meter']);
		Unit::updateOrCreate(['id'=> 7], ['code'=>strtoupper('ton'),'name'=>'Ton']);

    }

    public function provinces()
    {
		Province::updateOrCreate(['id'=> 1], ['name'=> 'Nanggroe Aceh Darussalam']);
		Province::updateOrCreate(['id'=> 2], ['name'=> 'Sumatera Utara']);
		Province::updateOrCreate(['id'=> 3], ['name'=> 'Sumatera Barat']);
		Province::updateOrCreate(['id'=> 4], ['name'=> 'Riau']);
		Province::updateOrCreate(['id'=> 5], ['name'=> 'Kepulauan Riau']);
		Province::updateOrCreate(['id'=> 6], ['name'=> 'Jambi']);
		Province::updateOrCreate(['id'=> 7], ['name'=> 'Sumatera Selatan']);
		Province::updateOrCreate(['id'=> 8], ['name'=> 'Bangka Belitung']);
		Province::updateOrCreate(['id'=> 9], ['name'=> 'Bengkulu']);
		Province::updateOrCreate(['id'=> 10], ['name'=> 'Lampung']);
		Province::updateOrCreate(['id'=> 11], ['name'=> 'DKI Jakarta']);
		Province::updateOrCreate(['id'=> 12], ['name'=> 'Jawa Barat']);
		Province::updateOrCreate(['id'=> 13], ['name'=> 'Banten']);
		Province::updateOrCreate(['id'=> 14], ['name'=> 'Jawa Tengah']);
		Province::updateOrCreate(['id'=> 15], ['name'=> 'D. I. Yogyakarta']);
		Province::updateOrCreate(['id'=> 16], ['name'=> 'Jawa Timur']);
		Province::updateOrCreate(['id'=> 17], ['name'=> 'Bali']);
		Province::updateOrCreate(['id'=> 18], ['name'=> 'Nusa Tenggara Barat']);
		Province::updateOrCreate(['id'=> 19], ['name'=> 'Nusa Tenggara Timur']);
		Province::updateOrCreate(['id'=> 20], ['name'=> 'Kalimantan Barat']);
		Province::updateOrCreate(['id'=> 21], ['name'=> 'Kalimantan Tengah']);
		Province::updateOrCreate(['id'=> 22], ['name'=> 'Kalimantan Selatan']);
		Province::updateOrCreate(['id'=> 23], ['name'=> 'Kalimantan Timur']);
		Province::updateOrCreate(['id'=> 24], ['name'=> 'Kalimantan Utara']);
		Province::updateOrCreate(['id'=> 25], ['name'=> 'Sulawesi Utara']);
		Province::updateOrCreate(['id'=> 26], ['name'=> 'Sulawesi Barat']);
		Province::updateOrCreate(['id'=> 27], ['name'=> 'Sulawesi Tengah']);
		Province::updateOrCreate(['id'=> 28], ['name'=> 'Sulawesi Tenggara']);
		Province::updateOrCreate(['id'=> 29], ['name'=> 'Sulawesi Selatan']);
		Province::updateOrCreate(['id'=> 30], ['name'=> 'Gorontalo']);
		Province::updateOrCreate(['id'=> 31], ['name'=> 'Maluku']);
		Province::updateOrCreate(['id'=> 32], ['name'=> 'Maluku Utara']);
		Province::updateOrCreate(['id'=> 33], ['name'=> 'Papua Barat']);
		Province::updateOrCreate(['id'=> 34], ['name'=> 'Papua']);

    }

	public function category_items()
    {
		CategoryItem::updateOrCreate(['id'=> 1], ['name'=>'GENERAL','description'=>'The description for genaral']);
		CategoryItem::updateOrCreate(['id'=> 2], ['name'=>'2-WHEEL','description'=>'The description for two-wheel vehicle']);
		CategoryItem::updateOrCreate(['id'=> 3], ['name'=>'4-WHEEL','description'=>'The description for our-wheel vehicle']);
        CategoryItem::updateOrCreate(['id'=> 4], ['name'=>'ELECTRONIC','description'=>'The description for Electronic']);
    }

	public function type_items()
    {
		TypeItem::updateOrCreate(['id'=> 1], ['name'=>'Regular','description'=>'This is Reguler']);
		TypeItem::updateOrCreate(['id'=> 2], ['name'=>'Non-Reguler','description'=>'This is Non-Reguler']);
	}

}
