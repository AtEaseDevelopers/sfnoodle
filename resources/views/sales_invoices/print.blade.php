<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    <style>
        @page { margin: 0; }
        html, body { page-break-inside: avoid; page-break-after: avoid; }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-size: 22px;
            font-family: 'Courier New', monospace;
            line-height: 1.2;
            padding: 10px;
        }

        .header-section {
            text-align: center;
            margin-bottom: 15px;
        }

        .company-name {
            font-weight: bold;
            font-size: 22px;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 18px;
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
            font-size: 20px;
        }
        
        .product-table {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }
        
        .product-table th,
        .product-table td {
            padding: 6px 4px;
            font-size: 20px;
            vertical-align: top;
        }
        
        .col-sku {
            width: 40%;
            text-align: left;
        }
        
        .col-qty {
            width: 18%;
            text-align: right;
        }
        
        .col-price {
            width: 22%;
            text-align: right;
        }
        
        .col-total {
            width: 20%;
            text-align: right;
        }
        
        .invoice-remark {
            font-size: 14px;
            margin: 10px 0;
        }
        
        .footer-line {
            border-top: 1px dashed #000;
            margin: 15px 0 10px 0;
        }
        
        .total-row {
            font-weight: bold;
            font-size: 30px;
            margin-top: 10px;
        }
        
    </style>
</head>
<body>
    <div class="header-section">
        <div class="invoice-title">============== SALES ORDER ============</div>
        <div class="company-name">{{ config('invoice.name', $salesInvoice->customer->groupcompany->name ?? 'SF NOODLES SON BHD') }}</div>
        <div class="company-details">(Formerly known as Soon Fatt Foods Sdn Bhd)</div>
        <div class="company-details">ROC.: {{ config('invoice.roc', '201001017887 (901592-A)') }}</div>
        <div class="company-details">{{ config('invoice.address1', '48, Jin TPP 1/18, Tim Industri Puchong,') }}</div>
        <div class="company-details">{{ config('invoice.address2', '47100 Puchong, Selangor DE.') }}</div>
        <div class="company-details">t: {{ config('invoice.phone', '03-8061 1490/ 012-311 1531') }}</div>
        <div class="company-details">email: {{ config('invoice.email', 'account@sfnoodles.com') }}</div>
    </div>
    
    <table class="info-table">
        <tr>
            <td class="left-align">Invoice Date</td>
            <td class="right-align">{{ $salesInvoice->date ? date_format(date_create($salesInvoice->date), 'd M Y') : '' }}</td>
        </tr>
        <tr>
            <td class="left-align">Invoice No:</td>
            <td class="right-align">{{ $salesInvoice->invoiceno ?? '' }}</td>
        </tr>
        <tr>
            <td class="left-align">Customer:</td>
            <td class="right-align">{{ $salesInvoice->customer->company ?? '' }}</td>
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
                <td class="col-sku">
                    {{ $item['display_name'] }}
                </td>
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
                <td class="left-align" style="font-weight: bold; font-size: 30px;">Total</td>
                <td class="right-align" style="font-weight: bold; font-size: 30px;">
                    RM {{ number_format($finalTotal, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-line"></div>
    
    @if($salesInvoice->remark)
    <div class="invoice-remark">REMARK : {{ $salesInvoice->remark }}</div>
    @endif
</body>
</html>