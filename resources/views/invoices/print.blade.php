<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <title>{{ config('app.name') }}</title>
    <style>
        @page { 
            margin: 0;
        }
        
        html, body { 
            page-break-inside: avoid; 
            page-break-after: avoid;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-size: 22px !important; /* Added !important */
            font-family: 'Segoe UI', 'Arial', sans-serif;
            line-height: 1.2;
            padding: 10px;
            /* Force stable rendering */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: geometricPrecision;
            /* Prevent font size adjustment on mobile browsers */
            -webkit-text-size-adjust: 100% !important;
            text-size-adjust: 100% !important;
        }

        .header-section {
            text-align: center;
            margin-bottom: 15px;
        }

        .company-name {
            font-weight: bold;
            font-size: 22px !important;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 18px !important;
            margin-bottom: 2px;
        }

        .invoice-title {
            font-size: 16px !important; /* Added !important */
            font-weight: bold;
            text-align: center;
            margin: 5px 0;
        }

        .section-separator {
            border-top: 1px dashed #000;
            margin: 15px 0;
        }

        .left-align {
            text-align: left;
        }

        .right-align {
            text-align: right;
        }

        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;
            font-size: 20px !important;
            vertical-align: top;
        }

        .info-table td:first-child {
            width: 140px;
            white-space: nowrap;
            vertical-align: top;
            padding-right: 10px;
        }

        .info-table td:last-child {
            white-space: normal;
            word-wrap: break-word;
            word-break: break-word;
            text-align: right;
        }

        .total-row .info-table td:first-child {
            width: auto;
            white-space: normal;
        }

        .total-row .info-table td:last-child {
            text-align: right;
        }

        /* CRITICAL: Added table-layout: fixed for performance */
        .product-table {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
            table-layout: fixed; /* This is key for performance with many rows */
        }

        .product-table th,
        .product-table td {
            padding: 6px 4px;
            font-size: 20px !important;
            vertical-align: top;
        }

        /* Fixed widths for table-layout: fixed to work properly */
        .col-sku {
            width: 40%;
            text-align: left;
            word-wrap: break-word;
        }

        .col-qty {
            width: 18%;
            text-align: right;
            padding-right: 20px !important;
        }

        .col-price {
            width: 22%;
            text-align: right;
            padding-right: 20px !important;
        }

        .col-total {
            width: 20%;
            text-align: right;
        }

        .invoice-remark {
            font-size: 14px !important; /* Added !important */
            margin: 10px 0;
        }

        .footer-line {
            border-top: 1px dashed #000;
            margin: 15px 0 10px 0;
        }

        .total-row {
            font-weight: bold;
            font-size: 30px !important; /* Added !important */
            margin-top: 10px;
        }
        
        .total-row .left-align,
        .total-row .right-align {
            font-size: 30px !important;
        }
        
        /* Optional: Prevent page breaks inside rows when printing */
        @media print {
            body {
                font-size: 22px !important;
            }
            
            .product-table tbody tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="invoice-title">========== TEMPORARY INVOICES ==========</div>
        <div class="company-name">{{ config('invoice.name', $invoices->customer->groupcompany->name ?? 'SF NOODLES SON BHD') }}</div>
        <div class="company-details">ROC.: {{ config('invoice.roc', '201001017887 (901592-A)') }}</div>
        <div class="company-details">{{ config('invoice.address1', '48, Jin TPP 1/18, Tim Industri Puchong,') }}</div>
        <div class="company-details">{{ config('invoice.address2', '47100 Puchong, Selangor DE.') }}</div>
        <div class="company-details">t: {{ config('invoice.phone', '03-8061 1490/ 012-311 1531') }}</div>
        <div class="company-details">email: {{ config('invoice.email', 'account@sfnoodles.com') }}</div>
    </div>
    
    <table class="info-table">
        <tr>
            <td class="left-align">Invoice Date:</td>
            <td class="right-align">{{ $invoices->date ? date_format(date_create($invoices->date), 'd M Y') : '' }}</td>
        </tr>
        <tr>
            <td class="left-align">Invoice No:</td>
            <td class="right-align">{{ $invoices->invoiceno ?? '' }}</td>
        </tr>
        <tr>
            <td class="left-align">Customer:</td>
            <td class="right-align">{{ $invoices->customer->company ?? '' }}</td>
        </tr>
        <tr>
            <td class="left-align">Created By:</td>
            <td class="right-align">{{ $creatorName ?? '' }}</td>
        </tr>
    </table>

    <table class="product-table">
        <thead>
            <tr>
                <th class="col-sku">SKU</th>
                <th class="col-qty">Qty</th>
                <th class="col-price">U.Price</th>
                <th class="col-total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($allItems as $item)
            <tr>
                <td class="col-sku">{{ $item['display_name'] }}</td>
                <td class="col-qty">{{ $item['quantity'] }}</td>
                <td class="col-price">{{ number_format($item['price'], 2) }}</td>
                <td class="col-total">{{ number_format($item['totalprice'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
     </table>

    <div class="section-separator"></div>

    <div class="total-row">
        <table class="info-table">
            <tr>
                <td class="left-align">Total</td>
                <td class="right-align">RM {{ number_format($finalTotal, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer-line"></div>
    
    @if($invoices->remark)
    <div class="invoice-remark">REMARK : {{ $invoices->remark }}</div>
    @endif
</body>
</html>