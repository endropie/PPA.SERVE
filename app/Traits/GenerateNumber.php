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
        $digit = 5;
        $prefix = 'FCO/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Income\Forecast::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextRequestOrderNumber($date = null)
    {
        $digit = 5;
        $prefix = 'POC/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Income\RequestOrder::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextPreDeliveryNumber($date = null)
    {
        $digit = 5;
        $prefix = 'PDO/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Income\PreDelivery::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextDeliveryNumber($date = null)
    {
        $digit = 5;
        $prefix = 'DO/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Income\Delivery::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextPackingNumber($date = null)
    {
        $digit = 5;
        $prefix = 'WPR/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Factory\Packing::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextWorkinProductionNumber($date = null)
    {
        $digit = 5;
        $prefix = 'WIP/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Factory\WorkinProduction::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextWorkOrderNumber($date = null)
    {
        $digit = 5;
        $prefix = 'WO/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Factory\WorkOrder::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextIncomingGoodNumber($date = null)
    {
        $digit = 5;
        $prefix = 'IG/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Warehouse\IncomingGood::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextFinishedGoodNumber($date = null)
    {
        $digit = 5;
        $prefix = 'FG/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = \App\Models\Warehouse\FinishedGood::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextPurchaseInvoiceNumber($date = null)
    {
        $digit = 5;
        $prefix = 'BIL/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = PurchaseInvoice::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextSalesQuotationNumber($date = null)
    {
        $digit = 5;
        $prefix = 'QO/{{Y-m}}/';

        $prefix = $this->dateParser($prefix, $date);
        
        $next = SalesQuotation::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextSalesOrderNumber($date = null)
    {
        $digit = 5;
        $prefix = 'SO/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = SalesOrder::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextSalesDeliveryNumber($date = null)
    {
        $digit = 5;
        $prefix = 'DO/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = SalesDelivery::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextSalesInvoiceNumber($date = null)
    {
        $digit = 5;
        $prefix = 'INV/{{Y-m}}/';
        
        $prefix = $this->dateParser($prefix, $date);
        
        $next = SalesInvoice::where('number','LIKE', $prefix.'%')->max('number');
        $next = $next ? (int) str_replace($prefix,'', $next) : 0;
        $next++;
        
        $number = $prefix . str_pad($next, $digit, '0', STR_PAD_LEFT);

        return $number;
    }

    public function getNextJournalNumber($date = null)
    {
        $digit = 5;
        $prefix = 'JE/{{Y-m}}/';
        
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