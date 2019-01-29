<?php

use Illuminate\Database\Seeder;
use App\Models\Reference\Province;

class ProvinceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
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
}
