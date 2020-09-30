<?php
return [

    "database" => env('ACCURATE_DATABASE', null),

    "login_route" => "/accurate/login",

    "callback_route" => "/accurate/callback",

    "redirect_autologin" => false,

    "redirect_back_route" => true,

    "redirect_callback_data" => true,

    "scope" => [
        "access_privilege_view", "access_privilege_view",
        "bank_transfer_view", "bank_transfer_save", "bank_transfer_delete",
        "item_view", "item_save",
        "branch_view", "branch_save",
        "department_view", "department_save",
        "employee_view", "employee_save",
        "customer_view", "customer_save",
        "vendor_view", "vendor_save",
        "project_view", "project_save",
        "expense_accrual_view", "expense_accrual_save",
        "glaccount_view", "glaccount_save",
        "journal_voucher_view", "journal_voucher_save",

        "delivery_order_view", "delivery_order_save",
        "sales_invoice_view", "sales_invoice_save",
        "sales_order_view", "sales_order_save",
        "sales_quotation_view", "sales_quotation_save",
        "sales_receipt_view", "sales_receipt_save",
        "sales_return_view", "sales_return_save",
        "purchase_invoice_view", "purchase_invoice_save",
        "purchase_order_view", "purchase_order_save",
        "purchase_payment_view", "purchase_payment_save",
        "purchase_requisition_view", "purchase_requisition_save",
        "purchase_return_view", "purchase_return_save",
    ],

    "modules" => [
        "access-privilege" => [
            "list" => "/accurate/api/access-privilege/list.do",
            "list" => "/accurate/api/access-privilege/detail.do"
        ],
        "auto-number" => [
            "list" => "/accurate/api/auto-number/list.do"
        ],
        "bank-transfer" => [
            "bulk-save" => "/accurate/api/bank-transfer/bulk-save.do",
            "delete" => "/accurate/api/bank-transfer/delete.do",
            "detail" => "/accurate/api/bank-transfer/detail.do",
            "list" => "/accurate/api/bank-transfer/list.do",
            "save" => "/accurate/api/bank-transfer/save.do",
        ],
        "branch" => [
            "bulk-save" => "/accurate/api/branch/bulk-save.do",
            "delete" => "/accurate/api/branch/delete.do",
            "detail" => "/accurate/api/branch/detail.do",
            "list" => "/accurate/api/branch/list.do",
            "save" => "/accurate/api/branch/save.do",
        ],
        "customer" => [
            "bulk-save" => "/accurate/api/customer/bulk-save.do",
            "delete" => "/accurate/api/customer/delete.do",
            "detail" => "/accurate/api/customer/detail.do",
            "list" => "/accurate/api/customer/list.do",
            "save" => "/accurate/api/customer/save.do",
        ],
        "delivery-order" => [
            "bulk-save" => "/accurate/api/delivery-order/bulk-save.do",
            "delete" => "/accurate/api/delivery-order/delete.do",
            "detail" => "/accurate/api/delivery-order/detail.do",
            "list" => "/accurate/api/delivery-order/list.do",
            "save" => "/accurate/api/delivery-order/save.do",
        ],
        "department" => [
            "bulk-save" => "/accurate/api/department/bulk-save.do",
            "delete" => "/accurate/api/department/delete.do",
            "detail" => "/accurate/api/department/detail.do",
            "list" => "/accurate/api/department/list.do",
            "save" => "/accurate/api/department/save.do",
        ],
        "employee" => [
            "delete" => "/accurate/api/employee/delete.do",
            "detail" => "/accurate/api/employee/detail.do",
            "list" => "/accurate/api/employee/list.do",
        ],
        "exchange-invoice" => [
            "bulk-save" => "/accurate/api/exchange-invoice/bulk-save.do",
            "delete" => "/accurate/api/exchange-invoice/delete.do",
            "detail" => "/accurate/api/exchange-invoice/detail.do",
            "list" => "/accurate/api/exchange-invoice/list.do",
            "save" => "/accurate/api/exchange-invoice/save.do",
        ],
        "expense" => [
            "bulk-save" => "/accurate/api/expense/bulk-save.do",
            "delete" => "/accurate/api/expense/delete.do",
            "detail" => "/accurate/api/expense/detail.do",
            "list" => "/accurate/api/expense/list.do",
            "save" => "/accurate/api/expense/save.do",
        ],
        "fixed-aset" => [
            "delete" => "/accurate/api/fixed-aset/delete.do",
            "detail" => "/accurate/api/fixed-aset/detail.do",
            "list" => "/accurate/api/fixed-aset/list.do",
        ],
        "fob" => [
            "bulk-save" => "/accurate/api/fob/bulk-save.do",
            "delete" => "/accurate/api/fob/delete.do",
            "detail" => "/accurate/api/fob/detail.do",
            "list" => "/accurate/api/fob/list.do",
            "save" => "/accurate/api/fob/save.do",
        ],
        "glaccount" => [
            "bulk-save" => "/accurate/api/glaccount/bulk-save.do",
            "delete" => "/accurate/api/glaccount/delete.do",
            "detail" => "/accurate/api/glaccount/detail.do",
            "list" => "/accurate/api/glaccount/list.do",
            "save" => "/accurate/api/glaccount/save.do",
        ],
        "item" => [
            "bulk-save" => "/accurate/api/item/bulk-save.do",
            "delete" => "/accurate/api/item/delete.do",
            "detail" => "/accurate/api/item/detail.do",
            "list" => "/accurate/api/item/list.do",
            "save" => "/accurate/api/item/save.do",
            "get-stock" => "/accurate/api/item/get-stock.do",
            "stock-mutation-history" => "/accurate/api/item/stock-mutation-history.do",
        ],
        "item-adjustment" => [
            "bulk-save" => "/accurate/api/item-adjustment/bulk-save.do",
            "delete" => "/accurate/api/item-adjustment/delete.do",
            "detail" => "/accurate/api/item-adjustment/detail.do",
            "list" => "/accurate/api/item-adjustment/list.do",
            "save" => "/accurate/api/item-adjustment/save.do",
        ],
        "item-transfer" => [
            "bulk-save" => "/accurate/api/item-transfer/bulk-save.do",
            "delete" => "/accurate/api/item-transfer/delete.do",
            "detail" => "/accurate/api/item-transfer/detail.do",
            "list" => "/accurate/api/item-transfer/list.do",
            "save" => "/accurate/api/item-transfer/save.do",
        ],
        "job-order" => [
            "bulk-save" => "/accurate/api/job-order/bulk-save.do",
            "delete" => "/accurate/api/job-order/delete.do",
            "detail" => "/accurate/api/job-order/detail.do",
            "list" => "/accurate/api/job-order/list.do",
            "save" => "/accurate/api/job-order/save.do",
        ],
        "journal-voucher" => [
            "bulk-save" => "/accurate/api/journal-voucher/bulk-save.do",
            "delete" => "/accurate/api/journal-voucher/delete.do",
            "detail" => "/accurate/api/journal-voucher/detail.do",
            "list" => "/accurate/api/journal-voucher/list.do",
            "save" => "/accurate/api/journal-voucher/save.do",
        ],
        "material-adjustment" => [
            "bulk-save" => "/accurate/api/material-adjustment/bulk-save.do",
            "delete" => "/accurate/api/material-adjustment/delete.do",
            "detail" => "/accurate/api/material-adjustment/detail.do",
            "list" => "/accurate/api/material-adjustment/list.do",
            "save" => "/accurate/api/material-adjustment/save.do",
        ],
        "other-deposit" => [
            "bulk-save" => "/accurate/api/other-deposit/bulk-save.do",
            "delete" => "/accurate/api/other-deposit/delete.do",
            "detail" => "/accurate/api/other-deposit/detail.do",
            "list" => "/accurate/api/other-deposit/list.do",
            "save" => "/accurate/api/other-deposit/save.do",
        ],
        "other-payment" => [
            "bulk-save" => "/accurate/api/other-payment/bulk-save.do",
            "delete" => "/accurate/api/other-payment/delete.do",
            "detail" => "/accurate/api/other-payment/detail.do",
            "list" => "/accurate/api/other-payment/list.do",
            "save" => "/accurate/api/other-payment/save.do",
        ],
        "payment-term" => [
            "bulk-save" => "/accurate/api/payment-term/bulk-save.do",
            "delete" => "/accurate/api/payment-term/delete.do",
            "detail" => "/accurate/api/payment-term/detail.do",
            "list" => "/accurate/api/payment-term/list.do",
            "save" => "/accurate/api/payment-term/save.do",
        ],
        "pos" => [
            "customer" => [
                "save" => "/accurate/api/customer/save.do",
            ],
            "item" => [
                "save" => "/accurate/api/item/save.do",
            ],
            "transaction" => [
                "save" => "/accurate/api/transaction/save.do",
            ]
        ],
        "project" => [
            "delete" => "/accurate/api/project/delete.do",
            "detail" => "/accurate/api/project/detail.do",
            "list" => "/accurate/api/project/list.do",
            "save" => "/accurate/api/project/save.do",
        ],
        "purchase-invoice" => [
            "bulk-save" => "/accurate/api/purchase-invoice/bulk-save.do",
            "delete" => "/accurate/api/purchase-invoice/delete.do",
            "detail" => "/accurate/api/purchase-invoice/detail.do",
            "list" => "/accurate/api/purchase-invoice/list.do",
            "save" => "/accurate/api/purchase-invoice/save.do",
        ],
        "purchase-order" => [
            "bulk-save" => "/accurate/api/purchase-order/bulk-save.do",
            "delete" => "/accurate/api/purchase-order/delete.do",
            "detail" => "/accurate/api/purchase-order/detail.do",
            "list" => "/accurate/api/purchase-order/list.do",
            "save" => "/accurate/api/purchase-order/save.do",
        ],
        "purchase-payment" => [
            "bulk-save" => "/accurate/api/purchase-payment/bulk-save.do",
            "delete" => "/accurate/api/purchase-payment/delete.do",
            "detail" => "/accurate/api/purchase-payment/detail.do",
            "list" => "/accurate/api/purchase-payment/list.do",
            "save" => "/accurate/api/purchase-payment/save.do",
        ],
        "purchase-requisition" => [
            "bulk-save" => "/accurate/api/purchase-requisition/bulk-save.do",
            "delete" => "/accurate/api/purchase-requisition/delete.do",
            "detail" => "/accurate/api/purchase-requisition/detail.do",
            "list" => "/accurate/api/purchase-requisition/list.do",
            "save" => "/accurate/api/purchase-requisition/save.do",
        ],
        "purchase-return" => [
            "bulk-save" => "/accurate/api/purchase-return/bulk-save.do",
            "delete" => "/accurate/api/purchase-return/delete.do",
            "detail" => "/accurate/api/purchase-return/detail.do",
            "list" => "/accurate/api/purchase-return/list.do",
            "save" => "/accurate/api/purchase-return/save.do",
        ],
        "receive-item" => [
            "bulk-save" => "/accurate/api/receive-item/bulk-save.do",
            "delete" => "/accurate/api/receive-item/delete.do",
            "detail" => "/accurate/api/receive-item/detail.do",
            "list" => "/accurate/api/receive-item/list.do",
            "save" => "/accurate/api/receive-item/save.do",
        ],
        "sales-invoice" => [
            "bulk-save" => "/accurate/api/sales-invoice/bulk-save.do",
            "delete" => "/accurate/api/sales-invoice/delete.do",
            "detail" => "/accurate/api/sales-invoice/detail.do",
            "list" => "/accurate/api/sales-invoice/list.do",
            "save" => "/accurate/api/sales-invoice/save.do",
        ],
        "sales-order" => [
            "bulk-save" => "/accurate/api/sales-order/bulk-save.do",
            "delete" => "/accurate/api/sales-order/delete.do",
            "detail" => "/accurate/api/sales-order/detail.do",
            "list" => "/accurate/api/sales-order/list.do",
            "save" => "/accurate/api/sales-order/save.do",
        ],
        "sales-quotation" => [
            "bulk-save" => "/accurate/api/sales-quotation/bulk-save.do",
            "delete" => "/accurate/api/sales-quotation/delete.do",
            "detail" => "/accurate/api/sales-quotation/detail.do",
            "list" => "/accurate/api/sales-quotation/list.do",
            "save" => "/accurate/api/sales-quotation/save.do",
        ],
        "sales-receipt" => [
            "bulk-save" => "/accurate/api/sales-receipt/bulk-save.do",
            "delete" => "/accurate/api/sales-receipt/delete.do",
            "detail" => "/accurate/api/sales-receipt/detail.do",
            "list" => "/accurate/api/sales-receipt/list.do",
            "save" => "/accurate/api/sales-receipt/save.do",
        ],
        "sales-return" => [
            "bulk-save" => "/accurate/api/sales-return/bulk-save.do",
            "delete" => "/accurate/api/sales-return/delete.do",
            "detail" => "/accurate/api/sales-return/detail.do",
            "list" => "/accurate/api/sales-return/list.do",
            "save" => "/accurate/api/sales-return/save.do",
        ],
        "sellingprice-adjustment" => [
            "detail" => "/accurate/api/sellingprice-adjustment/detail.do",
            "list" => "/accurate/api/sellingprice-adjustment/list.do",
        ],
        "shipment" => [
            "bulk-save" => "/accurate/api/shipment/bulk-save.do",
            "delete" => "/accurate/api/shipment/delete.do",
            "detail" => "/accurate/api/shipment/detail.do",
            "list" => "/accurate/api/shipment/list.do",
            "save" => "/accurate/api/shipment/save.do",
        ],
        "tax" => [
            "bulk-save" => "/accurate/api/tax/bulk-save.do",
            "delete" => "/accurate/api/tax/delete.do",
            "detail" => "/accurate/api/tax/detail.do",
            "list" => "/accurate/api/tax/list.do",
            "save" => "/accurate/api/tax/save.do",
        ],
        "unit" => [
            "bulk-save" => "/accurate/api/unit/bulk-save.do",
            "delete" => "/accurate/api/unit/delete.do",
            "detail" => "/accurate/api/unit/detail.do",
            "list" => "/accurate/api/unit/list.do",
            "save" => "/accurate/api/unit/save.do",
        ],
        "vendor" => [
            "bulk-save" => "/accurate/api/vendor/bulk-save.do",
            "delete" => "/accurate/api/vendor/delete.do",
            "detail" => "/accurate/api/vendor/detail.do",
            "list" => "/accurate/api/vendor/list.do",
            "save" => "/accurate/api/vendor/save.do",
        ],
        "warehouse" => [
            "list" => "/accurate/api/warehouse/list.do",
        ],
    ]
];
