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

    public function getNextPreDeliveryNumber($date = null)
    {
        $modul = 'pre_delivery';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul);
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Income\PreDelivery::withTrashed()->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextSJDeliveryNumber($date = null)
    {
        $modul = 'sj_delivery';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul);
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Income\DeliveryOrder::withTrashed()->where('number','LIKE', $prefix.'%')->max('number');
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

        $next = \App\Models\Factory\Packing::withTrashed()->where('number','LIKE', $prefix.'%')->max('number');
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

        $next = \App\Models\Factory\WorkProduction::withTrashed()->where('number','LIKE', $prefix.'%')->max('number');
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

        $next = \App\Models\Factory\WorkOrder::withTrashed()->where('number','LIKE', $prefix.'%')->max('number');
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

        $next = \App\Models\Warehouse\IncomingGood::withTrashed()->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextOpnameStockNumber($date = null)
    {
        $modul = 'opname_stock';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul);
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Warehouse\IncomingGood::withTrashed()->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextOutgoingGoodNumber($date = null)
    {
        $modul = 'outgoing_good';
        $digit = (int) setting()->get("$modul.number_digit", 5);
        $prefix = $this->prefixParser($modul);
        $prefix = $this->dateParser($prefix, $date);

        $next = \App\Models\Warehouse\OutgoingGood::withTrashed()->where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;

        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    protected function prefixParser($modul)
    {
        $prefix = '';
        if (setting()->get("$modul.number_prefix"))
            $prefix .= setting()->get("$modul.number_prefix") . setting()->get("general.prefix_separator");
        if (setting()->get("$modul.number_interval"))
            $prefix .= setting()->get("$modul.number_interval") . setting()->get("general.prefix_separator");

        return $prefix;
    }

    protected function dateParser($str, $date)
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

    public function toAlpha($num, $code = '')
    {
        $alphabets = array('', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');

        $division = floor($num / 26);
        $remainder = $num % 26;

        if($remainder == 0)
        {
            $division = $division - 1;
            $code .= 'z';
        }
        else
            $code .= $alphabets[$remainder];

        if($division > 26)
            return number_to_alpha($division, $code);
        else
            $code .= $alphabets[$division];

        return strrev($code);
    }
}
