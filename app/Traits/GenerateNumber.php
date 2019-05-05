<?php
namespace App\Traits;
use App\Models\Purchase\PurchaseInvoice;
use App\Models\Sales\SalesQuotation;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesDelivery;
use App\Models\Sales\SalesInvoice;
use App\Models\Accounting\Journal;

trait GenerateNumber
{
    public function getNextForecastNumber($date = null)
    {
        $digit = 6;
        $prefix = 'FC/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Income\Forecast::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextRequestOrderNumber($date = null)
    {
        $digit = 6;
        $prefix = 'SO/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Income\RequestOrder::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextPreDeliveryNumber($date = null)
    {
        $digit = 6;
        $prefix = 'PDO/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Income\PreDelivery::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextShipDeliveryNumber($date = null)
    {
        $digit = 6;
        $prefix = 'SDO/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Income\ShipDelivery::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextDeliveryOrderNumber($date = null)
    {
        $digit = 6;
        $prefix = 'DO/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Income\DeliveryOrder::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextPackingNumber($date = null)
    {
        $digit = 6;
        $prefix = 'WPR/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Factory\Packing::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextWorkinProductionNumber($date = null)
    {
        $digit = 6;
        $prefix = 'WIP/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Factory\WorkinProduction::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextWorkOrderNumber($date = null)
    {
        $digit = 6;
        $prefix = 'WO/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Factory\WorkOrder::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextIncomingGoodNumber($date = null)
    {
        $digit = 6;
        $prefix = 'MP/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Warehouse\IncomingGood::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextPurchaseInvoiceNumber($date = null)
    {
        $digit = 6;
        $prefix = 'BIL/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = PurchaseInvoice::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextSalesQuotationNumber($date = null)
    {
        $digit = 6;
        $prefix = 'QO/{{Y}}/';

        $prefix = $this->dateParser($prefix, $date);
        
        $next = SalesQuotation::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextSalesOrderNumber($date = null)
    {
        $digit = 6;
        $prefix = 'SO/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = SalesOrder::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextSalesDeliveryNumber($date = null)
    {
        $digit = 6;
        $prefix = 'DO/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = SalesDelivery::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextSalesInvoiceNumber($date = null)
    {
        $digit = 6;
        $prefix = 'INV/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = SalesInvoice::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextJournalNumber($date = null)
    {
        $digit = 6;
        $prefix = 'JE/{{Y}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = Journal::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function dateParser($str, $date)
    {
        $start = "{{";
        $end   = "}}";
        $matches = array();
        $regex = "/{{(.*)}}/";

        $date  = $date ? $date : date('Y-m-d');
        preg_match_all($regex, $str, $matches);
        
        if(count($matches) > 0)
        {
            $str = str_replace($matches[0][0], date($matches[1][0], strtotime($date)) , $str);
        }
        
        return $str;
    }
}