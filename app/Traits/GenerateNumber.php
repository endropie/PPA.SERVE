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