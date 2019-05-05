<?php

namespace App\Models\Common;

use App\Models\Model;
use App\Filters\Filterable;

class Item extends Model
{
   use Filterable;

   protected $fillable = [
      'code', 'customer_id', 'brand_id', 'specification_id', 'part_name', 'part_alias',  'part_number',
      'packing_duration', 'sa_area', 'weight', 'number_hanger', 'price', 
      'category_item_id', 'type_item_id', 'size_id', 'unit_id', 'description'
   ];

   protected $appends = [];

   protected $hidden = ['created_at', 'updated_at'];

   protected $model_relations = ['incoming_good_items'];

   public function item_prelines()
   {
      return $this->hasMany('App\Models\Common\ItemPreline');
   }

   public function item_units()
   {
      return $this->hasMany('App\Models\Common\ItemUnit');
   }

   public function item_stocks()
   {
      return $this->hasMany('App\Models\Common\ItemStock');
   }
   
   public function unit()
   {
      return $this->belongsTo('App\Models\Reference\Unit');
   }

   public function customer()
   {
      return $this->belongsTo('App\Models\Income\Customer');
   }

   public function brand()
   {
      return $this->belongsTo('App\Models\Reference\Brand');
   }

   public function specification()
   {
      return $this->belongsTo('App\Models\Reference\Specification');
   }

   public function getTotalsAttribute()
   {
      $stocks = [];
      foreach (ItemStockist::toArray() as $key => $value) {
         $stocks[$key] = $this->hasMany('App\Models\Common\ItemStock')->where('stockist', $value)->sum('total');
      }
      return $stocks;
   }

   public function stock($stockist)
   {
      $stockist = ItemStockist::getValidStockist($stockist);

      return $this->item_stocks->where('stockist', $stockist)->first();
   }


   // Execute Function for INCREASE & DECREASE total stock in `item_stocks` model
   // ===========================================================================
   // $number as quantity calculate stock,
   // $stockist as enum/section of stock, 
   // $exStockist as where taking stockist/enum/section of Stock 
   // (default $exStockist => false)
   public function increase($number, $stockist, $exStockist = false)
   {
      if($exStockist) {
         $exStockist = ItemStockist::getValidStockist($exStockist);

         // $exStock = $this->item_stocks->where('stockist', $exStockist)->first();

         // if(!$exStock) $exStock = $this->item_stocks()->create(['stockist' => $exStockist, 'total' => 0]);

         $exStock = $this->item_stocks()->firstOrCreate(['stockist' => $exStockist]);

         $exStock->total = $exStock->total - $number;

         $exStock->save();
      }

      $stockist = ItemStockist::getValidStockist($stockist);

      // $stock = $this->stock($stockist);

      // if(!$stock) $stock = $this->item_stocks()->create(['stockist' => $stockist, 'total' => 0]);

      $stock = $this->item_stocks()->firstOrCreate(['stockist' => $stockist]);

      $stock->total = $stock->total + $number;

      $stock->save();

      return $stock;
   }

   public function decrease($number, $stockist, $exStockist = false)
   {
      if($exStockist) {
         $exStockist = ItemStockist::getValidStockist($exStockist);

         // $exStock = $this->item_stocks->where('stockist', $exStockist)->first();
         // if(!$exStock) $exStock = $this->item_stocks()->create(['stockist' => $exStockist, 'total' => 0]);

         $exStock = $this->item_stocks()->firstOrCreate(['stockist' => $exStockist]);

         $exStock->total = $exStock->total + $number;

         $exStock->save();
      }

      $stockist = ItemStockist::getValidStockist($stockist);

      // $stock = $this->item_stocks->where('stockist', $stockist)->first();
      // if(!$stock) $stock = $this->item_stocks()->create(['stockist' => $stockist, 'total' => 0]);

      $stock = $this->item_stocks()->firstOrCreate(['stockist' => $stockist]);

      $stock->total = $stock->total - $number;

      $stock->save();

      return $stock;
   }
}
 