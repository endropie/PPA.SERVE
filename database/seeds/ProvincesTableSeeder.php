<?php

use Illuminate\Database\Seeder;
use App\Models\Reference\Province;

class ProvincesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		
        // Province::delete();
		
		Province::insert(['id'=> 1, 'name'=> 'Nanggroe Aceh Darussalam']);
		Province::insert(['id'=> 2, 'name'=> 'Sumatera Utara']);
		Province::insert(['id'=> 3, 'name'=> 'Sumatera Barat']);
		Province::insert(['id'=> 4, 'name'=> 'Riau']);
		Province::insert(['id'=> 5, 'name'=> 'Kepulauan Riau']);
		Province::insert(['id'=> 6, 'name'=> 'Jambi']);
		Province::insert(['id'=> 7, 'name'=> 'Sumatera Selatan']);
		Province::insert(['id'=> 8, 'name'=> 'Bangka Belitung']);
		Province::insert(['id'=> 9, 'name'=> 'Bengkulu']);
		Province::insert(['id'=> 10, 'name'=> 'Lampung']);
		Province::insert(['id'=> 11, 'name'=> 'DKI Jakarta']);
		Province::insert(['id'=> 12, 'name'=> 'Jawa Barat']);
		Province::insert(['id'=> 13, 'name'=> 'Banten']);
		Province::insert(['id'=> 14, 'name'=> 'Jawa Tengah']);
		Province::insert(['id'=> 15, 'name'=> 'D. I. Yogyakarta']);
		Province::insert(['id'=> 16, 'name'=> 'Jawa Timur']);
		Province::insert(['id'=> 17, 'name'=> 'Bali']);
		Province::insert(['id'=> 18, 'name'=> 'Nusa Tenggara Barat']);
		Province::insert(['id'=> 19, 'name'=> 'Nusa Tenggara Timur']);
		Province::insert(['id'=> 20, 'name'=> 'Kalimantan Barat']);
		Province::insert(['id'=> 21, 'name'=> 'Kalimantan Tengah']);
		Province::insert(['id'=> 22, 'name'=> 'Kalimantan Selatan']);
		Province::insert(['id'=> 23, 'name'=> 'Kalimantan Timur']);
		Province::insert(['id'=> 24, 'name'=> 'Kalimantan Utara']);
		Province::insert(['id'=> 25, 'name'=> 'Sulawesi Utara']);
		Province::insert(['id'=> 26, 'name'=> 'Sulawesi Barat']);
		Province::insert(['id'=> 27, 'name'=> 'Sulawesi Tengah']);
		Province::insert(['id'=> 28, 'name'=> 'Sulawesi Tenggara']);
		Province::insert(['id'=> 29, 'name'=> 'Sulawesi Selatan']);
		Province::insert(['id'=> 30, 'name'=> 'Gorontalo']);
		Province::insert(['id'=> 31, 'name'=> 'Maluku']);
		Province::insert(['id'=> 32, 'name'=> 'Maluku Utara']);
		Province::insert(['id'=> 33, 'name'=> 'Papua Barat']);
		Province::insert(['id'=> 34, 'name'=> 'Papua']);

    }
}
