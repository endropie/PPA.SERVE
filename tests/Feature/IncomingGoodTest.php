<?php

namespace Tests\Feature;

use App\Models\Warehouse\IncomingGood;
use App\Models\Income\Customer;
use App\Models\Common\Item;
use App\Models\Reference\Vehicle;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

use function GuzzleHttp\json_encode;

class IncomingGoodTest extends TestCase
{
    use WithoutMiddleware;

    public function testStore()
    {
        $loop = 100;
        for ($i=0; $i < $loop; $i++) {
            $rs = $this->getNewRecord();
            if (!$rs['incoming_good_items']->count()) continue;
            // $rs = $this->getNewRecord(['transaction' => 'RETURN', 'order_mode' => 'PO']);

            $response = $this->json('POST', '/api/v1/warehouses/incoming-goods', $rs->toArray());

            if($response->getStatusCode() != 200) {
                $response->dump();
            }
            $newdata = json_decode($response->getContent());
            $response
                ->assertOK()
                ->assertJson([
                    'order_mode'  => $rs['transaction'] == 'RETURN' ? 'NONE' : $rs['order_mode'],
                    // ==> order_mode is must "NONE" when transaction is "RETURN".
                ]);


            $incoming_good = IncomingGood::findOrFail($newdata->id);
            foreach ($incoming_good->incoming_good_items as $detail) {
                $this->assertTrue($detail->item->customer_id == $incoming_good->customer_id);
                // ==> Part Item is customer has items.
            }

        }
    }

    public function testValidation() {
        $keys = IncomingGood::where('status','OPEN')->get()
            // ->random(5)
            ->pluck('id');
        $this->setValidation($keys);
    }

    protected function setValidation($keys) {

        $incoming_goods = IncomingGood::whereIn('id',$keys)->get();

        foreach ($incoming_goods as $incoming_good) {

            $incoming_good->incoming_good_items = $incoming_good->incoming_good_items->map(function($detail) {
                $detail->valid = $detail->quantity;
                return $detail;
            });

            $response = $this->json('PATCH', '/api/v1/warehouses/incoming-goods/'.$incoming_good->id.'?mode=validation', $incoming_good->toArray());

            if($response->getStatusCode() != 200) {
                $response->dump();
            }
            $newdata = json_decode($response->getContent());

            $response
                ->assertOk()
                ->assertJson([
                    'status' => 'VALIDATED'
                ]);

            // dd($newdata);
        }
    }

    private function getNewRecord ($def = []) {

        $customer = Customer::has('customer_items')->get()->random(1)->first();
        $vehicle = Vehicle::all()->random(1)->first();

        $date = \Carbon::now()->subDays(rand(0, 7));

        $transaction = rand(0,10) > 2 ? 'REGULER' : 'RETURN';
        $order_mode = $transaction == 'RETURN' ? 'NONE' : $customer->order_mode;

        $rs = [
            "transaction" => $transaction,
            "order_mode" => $order_mode,
            "customer_id" => $customer->id,
            "registration" => '#'. $date->format('md') . mt_rand(1111,99999),
            "date" => $date->format('Y-m-d'),
            "time" => $date->format('h:i:s'),
            "reference_number" => $customer->code .'/'. mt_rand(11,9999) .'/'. mt_rand(111,99999),
            "reference_date" => $date->subDays(rand(0, 2))->format('Y-m-d'),
            "vehicle_id" => $vehicle->id,
        ];

        if(gettype($def) == 'array')  $rs = array_merge($rs, $def);

        $rs['incoming_good_items'] = collect([]);

        $unitpre = collect([1,2,4,5])->random(1)->first();
        if ($rs['transaction'] == 'RETURN') $unitpre / 10;


        $items = $customer->customer_items->filter(function ($item) {
            if ($item->item_prelines->count() <= 0) return false;
            return $item->enable;
        });

        $items = $items->random(rand(0, $items->count() < 10 ? $items->count() : 5));

        foreach ($items->all() as $item) {
            $rs['incoming_good_items']->push([
                'item_id' => $item->id,
                'unit_id' => $item->unit_id,
                'unit_rate' => 1,
                'quantity' => (int) rand(1,10) * 100 / $unitpre
            ]);
        }


        return collect($rs);
    }

}
