<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{config('app.name')}}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body{
            font-size: 18px;
            margin: 0;
            padding: 0 5px; /* Add overall body padding */
            font-family: 'Courier New', monospace;
            line-height: 1.2;
            box-sizing: border-box;
        }
        table{
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table th, table td{
            padding: 2px 0;
            font-size: 18px;
            vertical-align: top;
        }
       .header-section {
            text-align: center;
            margin-bottom: 10px;
        }
        .company-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 3px;
        }
        .company-details {
            font-size: 14px;
            margin-bottom: 2px;
        }
        .invoice-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 5px 0;
        }
        .section-separator {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .left-align {
            text-align: left;
        }
        .right-align {
            text-align: right;
        }
        .center-align {
            text-align: center;
        }
        .total-section {
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .product-table {
            margin: 5px 0;
            /* Add padding to the entire table */
            width: calc(100% - 30px); /* Compensate for padding */
            margin-left: auto;
            margin-right: auto;
        }
       
        .invoice-remark {
            font-size: 13px;
            text-align: start;
            margin: 5px 0;
        }
        .col-sku {
            width: 40%;
        }
        .col-qty {
            width: 20%;
            text-align: right;
        }
        .col-price {
            width: 20%;
            text-align: right;
        }
        .col-total {
            width: 20%;
            text-align: right;
        }
        .footer-line {
            border-top: 1px dashed #000;
            margin: 10px 0 5px 0;
        }
        .thank-you {
            text-align: center;
            font-weight: bold;
            margin-top: 5px;
        }
        
    </style>
</head>
<body>
    <div class="header-section">
        <div class="invoice-title">=============== INVOICES ===============</div>
            <div class="section-separator"></div>

        <div class="company-name">{{ config('invoice.name', $invoices['customer']['groupcompany']->name ?? 'SF NOODLES SON BHD') }}</div>
        <div class="company-details">(Formerly known as Soon Fatt Foods Sdn Bhd)</div>
        <div class="company-details">ROC.: {{ config('invoice.roc', '201001017887 (901592-A)') }}</div>
        <div class="company-details">{{ config('invoice.address1', '48, Jin TPP 1/18, Tim Industri Puchong,') }}</div>
        <div class="company-details">{{ config('invoice.address2', '47100 Puchong, Selangor DE.') }}</div>
        <div class="company-details">t: {{ config('invoice.phone', '03-8061 1490/ 012-311 1531') }}</div>
        <div class="company-details">email: {{ config('invoice.email', 'account@sfnoodles.com') }}</div>
    </div>
    <div class="section-separator"></div>

    <table>
        <tr>
            <td class="left-align">Invoice Date</td>
            <td class="right-align">{{ date_format(date_create($invoices['date']),'d M Y') ?? '' }}</td>
        </tr>
        <tr>
            <td class="left-align">Invoice No:</td>
            <td class="right-align">{{ $invoices['invoiceno'] ?? '' }}</td>
        </tr>
    </table>

    <div class="section-separator"></div>

    <table>
        <tr>
            <td class="left-align">Customer:</td>
            <td class="right-align">{{ $invoices['customer']['company'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="left-align">Created By:</td>
            <td class="right-align">{{ $creatorName ?? ''}}</td>
        </tr>
    </table>

    <div class="section-separator"></div>

    <!-- Items Table Section -->
    <div class="table-container">
        <table class="product-table">
            <tr>
                <th class="col-sku left-align">SKU</th>
                <th class="col-qty" style="padding-right: 10px;">Qty</th>
                <th class="col-price">U.Price</th>
                <th class="col-total">Total</th>
            </tr>
            
            @php
                // Use the pre-calculated allItems array if available, otherwise calculate on the fly
                if (!isset($allItems)) {
                    $purchasedItems = [];
                    foreach ($invoices['invoiceDetails'] as $invoiceDetail) {
                        $purchasedItems[] = [
                            'product_id' => $invoiceDetail['product_id'],
                            'quantity' => $invoiceDetail['quantity'],
                            'price' => $invoiceDetail['price']
                        ];
                    }
                    $focItems = App\Models\Foc::calculateFocItems($invoices['customer_id'], $purchasedItems);
                    $allItems = [];
                    
                    foreach ($invoices['invoiceDetails'] as $invoiceDetail) {
                        $allItems[] = [
                            'product_code' => $invoiceDetail['product']['code'],
                            'quantity' => $invoiceDetail['quantity'],
                            'price' => $invoiceDetail['price'],
                            'totalprice' => $invoiceDetail['totalprice'],
                            'is_foc' => false
                        ];
                    }
                    
                    foreach ($focItems as $focItem) {
                        $allItems[] = [
                            'product_code' => $focItem['product_code'],
                            'quantity' => $focItem['quantity'],
                            'price' => 0,
                            'totalprice' => 0,
                            'is_foc' => true
                        ];
                    }
                }
            @endphp
            
            @foreach ($allItems as $item)
            <tr>
                <td class="col-sku left-align">
                    {{ $item['product_code'] }}@if($item['is_foc']) (FOC)@endif
                </td>
                <td class="col-qty" style="padding-right: 10px;">{{ $item['quantity'] }}</td>
                <td class="col-price">{{ number_format($item['price'], 2) }}</td>
                <td class="col-total">{{ number_format($item['totalprice'], 2) }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="section-separator"></div>

    <table>
        <tr>
            <td class="left-align" style="font-weight: bold; font-size: 30px;">Total</td>
            <td class="right-align" style="font-weight: bold; font-size: 30px;">
                RM {{ number_format($totalAmount ?? $totalamount, 2) }}
            </td>
        </tr>
    </table>


    <div class="footer-line"></div>
    
    <div class="invoice-remark">@if($invoices->remark)REMARK : {{$invoices->remark}}@endif</div>

</body>
</html>