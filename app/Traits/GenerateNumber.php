<?php
namespace App\Traits;

trait GenerateNumber
{
    public function getNextForecastNumber($date = null)
    {
        $modul = 'forecast';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul);
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Income\Forecast::withTrashed()->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextRequestOrderNumber($date = null)
    {
        $modul = 'request_order';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul);
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Income\RequestOrder::withTrashed()->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextAccInvoiceNumber($date = null)
    {
        $modul = 'acc_invoice';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul, 'RSI', '{Y-m}');
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Income\AccInvoice::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextDeliveryTaskNumber($date = null)
    {
        $modul = 'delivery_task';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul, "PDO", "{Y-m}");
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Income\DeliveryTask::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');

        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextDeliveryLoadNumber($date = null)
    {
        $modul = 'delivery_load';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul, "PLD", "{Y-m}");
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Income\DeliveryLoad::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');
        // ->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextSJDeliveryIndexedNumber($date = null, $prefix)
    {
        $modul = 'sj_delivery';
        $digit = (int) setting()->get("$modul.indexed_number_digit", 3);
        $interval = setting()->get("$modul.indexed_number_interval", '{Y-m}');
        $separator = setting()->get("general.prefix_separator", '/');

        if (strlen($interval)) $prefix = $prefix . $separator . $interval;
        $prefix.= $separator;

        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Income\DeliveryOrder::withTrashed()
            ->selectRaw('MAX(REPLACE(indexed_number, "'.$prefix.'", "") * 1) AS N')
            ->where('indexed_number','LIKE', $prefix.'%')->get()->max('N');

        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextSJDeliveryNumber($date = null)
    {
        $modul = 'sj_delivery';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul, 'SJDO');
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Income\DeliveryOrder::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');

        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextSJInternalNumber($date = null)
    {
        $modul = 'sj_internal';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul, 'DI');
        $prefix = $this->dateParser($prefix, $date, '');

        $next = \App\Models\Income\DeliveryOrder::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');
            // ->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextDeliveryInternalNumber($date = null)
    {
        $modul = 'delivery_internal';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul, 'DI', '{Y-m}');
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Income\DeliveryInternal::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');

        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextPackingNumber($date = null)
    {
        $modul = 'packing';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul);
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Factory\Packing::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');

        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextPackingLoadNumber($date = null)
    {
        $modul = 'packing_load';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul);
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Factory\PackingLoad::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');

        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextWorkProductionNumber($date = null)
    {
        $modul = 'work_production';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul);
        $prefix = $this->dateParser($prefix, $date);

        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Factory\WorkProduction::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextWorkOrderNumber($date = null)
    {
        $modul = 'work_order';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul);
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Factory\WorkOrder::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextIncomingGoodNumber($date = null)
    {
        $modul = 'incoming_good';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul);
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Warehouse\IncomingGood::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');
            // ->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextIncomingGoodIndexedNumber($date = null, $prefix)
    {
        $modul = 'incoming_good';
        $digit = (int) setting()->get("$modul.indexed_number_digit", 3);
        $interval = setting()->get("$modul.indexed_number_interval", '{Y-m}');
        $separator = setting()->get("general.prefix_separator", '/');

        if (strlen($interval)) $prefix = $prefix . $separator . $interval;
        $prefix.= $separator;

        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Warehouse\IncomingGood::withTrashed()
            ->selectRaw('MAX(REPLACE(indexed_number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextDeliveryHandoverNumber($date = null)
    {
        $modul = 'deportation_good';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul, 'DST', '{Y-m}');
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Income\DeliveryHandover::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');
            // ->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;

    }

    public function getNextDeportationGoodNumber($date = null)
    {
        $modul = 'deportation_good';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul, 'DPP', '{Y-m}');
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Warehouse\DeportationGood::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');
            // ->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextOpnameNumber($date = null)
    {
        $modul = 'opname_stock';
        $digit = (int) setting()->get("$modul.number_digit", 2);
        $prefix = $this->prefixParser($modul, "STO", "{Y}");
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Warehouse\Opname::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');
            // ->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextOpnameVoucherNumber($date = null)
    {
        $modul = 'opname_voucher';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul, "VSO", "{Y}");
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Warehouse\OpnameVoucher::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');
            // ->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextScheduleBoardNumber($date = null)
    {
        $modul = 'schedule_board';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul);
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Transport\ScheduleBoard::withTrashed()
            ->selectRaw('MAX(REPLACE(number, "'.$prefix.'", "") * 1) AS N')
            ->where('number','LIKE', $prefix.'%')->get()->max('N');
            // ->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    protected function prefixParser ($modul, $default_prefix = '', $default_interval = '{Y}')
    {
        $result_number = '';
        if ($code = setting()->get("$modul.number_prefix", $default_prefix)) {
            $result_number .= $code . setting()->get("general.prefix_separator",'/');
        }

        if ($interval = setting()->get("$modul.number_interval", $default_interval)) {
            $result_number .= $interval . setting()->get("general.prefix_separator",'/');
        }

        return $result_number;
    }

    protected function dateParser ($str, $date)
    {
        $matches = array();
        $regex = "/{(.*)}/";

        $date  = $date ? $date : date('Y-m-d');
        preg_match_all($regex, $str, $matches);

        if(count($matches) > 0 && count($matches[0]) > 0 )
        {
            $str = str_replace($matches[0][0], date($matches[1][0], strtotime($date)) , $str);
        }

        return $str ?? '';
    }
}
