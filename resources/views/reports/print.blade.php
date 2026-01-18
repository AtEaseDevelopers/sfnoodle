<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trip Summary Report</title>
    <style>
        @page {
            margin: 100px 25px;
            padding: 0;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        
        /* Fixed Header that repeats on every page */
        .header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 70px;
            text-align: start;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .company-info {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-weight: bold;
            margin: 10px 0;
        }
        
        /* Main content starts after header */
        .content {
            margin-top: 5px;
        }
        
        .trip-info {
            width: 100%;
        }
        
        .trip-info td {
            width: 50%; /* Two equal columns */
            padding: 4px 0;
            text-align: left;
            vertical-align: top;
            border:none;
            font-size: 12px;

        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8px;
            page-break-inside: auto;
        }
        
        tr {
            page-break-inside: auto;
            page-break-after: auto;
        }
        
        thead {
            display: table-header-group;
        }
        
        th, td {
            font-size: 12px;
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            page-break-inside: auto; /* Allow cells to break */
            page-break-before: auto;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #e0e0e0;
        }
        
        .section-title {
            font-weight: bold;
            margin: 10px 0 5px 0; /* Reduced margins */
            border-bottom: 1px solid #333;
            padding-bottom: 3px;
            page-break-after: avoid;
        }
        
        .negative {
            color: #b11f1fff;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        /* Ensure tables don't break awkwardly across pages */
        .table-container {
            page-break-inside: auto;
        }
        
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
    </style>
</head>
<body>
    <!-- Header that repeats on every page -->
    <div class="header">
        <div class="company-info">
        <span class="company-name">{{ $company_name }}</span> {{ $roc_no }}<br>
            {{ $address }}
            {{ $phone }} &nbsp;&nbsp; {{ $email }}
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="content">
        
        <div class="report-title">TRIP SUMMARY</div>

        <!-- Trip Information -->
        <table class="trip-info">
            <tr>
                <td><strong>Agent :</strong> {{ $salesman }}</td>
                <td><strong>Trip ID :</strong> {{ $trip_id }}</td>
            </tr>
            <tr>
                <td><strong>Printed Time :</strong> {{ $printed_time }}</td>
                <td><strong>Start Time :</strong> {{ $start_time }}</td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2"><strong>End Time :</strong> {{ $end_time }}</td>
            </tr>
        </table>
        <!-- Sales Summary -->
        <div class="section-title">Sales Summary</div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>UOM</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales_summary as $item)
                    <tr>
                        <td>{{ $item['code'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ $item['uom'] }}</td>
                        <td>RM {{ $item['amount'] }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td><strong>Total</strong></td>
                        <td><strong>{{ $total_quantity }}</strong></td>
                        <td></td>
                        <td><strong>{{ $total_amount }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Stock Summary -->
        @if(count($stock_summary) > 0)
            <div class="section-title">Stocks Summary</div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Brand</th>
                            <th>Open</th>
                            <th>StockIn</th>
                            <th>Sales</th>
                            <th>Return</th>
                            <th>Variance</th>
                            <th>StockOut</th>
                            <th>Close</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stock_summary as $item)
                        <tr>
                            <td>{{ $item['brand'] }}</td>
                            <td>{{ $item['open'] }}</td>
                            <td>{{ $item['stock_in'] }}</td>
                            <td class="{{ $item['sales'] < 0 ? 'negative' : '' }}">{{ $item['sales'] }}</td>
                            <td>{{ $item['return'] }}</td>
                            <td>{{ $item['variance'] }}</td>
                            <td>{{ $item['stock_out'] }}</td>
                            <td>{{ $item['close'] }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td><strong>Total</strong></td>
                            <td><strong>{{ $total_open }}</strong></td>
                            <td><strong>{{ $total_stock_in }}</strong></td>
                            <td class="{{ $total_sales < 0 ? 'negative' : '' }}"><strong>{{ $total_sales }}</strong></td>
                            <td><strong>{{ $total_return }}</strong></td>
                            <td><strong>{{ $total_variance }}</strong></td>
                            <td><strong>{{ $total_stock_out }}</strong></td>
                            <td><strong>{{ $total_close }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
        
        <!-- Invoice List -->
        @if(count($documents_list) > 0)
            @php
                $chunks = array_chunk($documents_list, 25); // Split documents into chunks
            @endphp
            
            @foreach($chunks as $chunkIndex => $chunk)
                @if($chunkIndex > 0)
                    <div class="page-break"></div>
                @endif
                
                <div class="section-title">
                    @if($loop->first)
                        Invoice List
                    @else
                        Invoice List (continued)
                    @endif
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Doc No.</th>
                                <th>Status</th>
                                <th>Company Name</th>
                                <th>Payment Term</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($chunk as $item)
                            <tr>
                                <td>{{ $item['doc_no'] }}</td>
                                <td>{{ $item['status'] }}</td>
                                <td>{{ $item['company_name'] }}</td>
                                <td>{{ $item['paymentterm'] }}</td>
                                <td>{{ $item['amount'] }}</td>
                            </tr>
                            @endforeach
                            
                            @if($loop->last)
                            <tr class="total-row">
                                <td colspan="4" style="text-align:left;">
                                    <strong>Number Of Documents</strong><br>
                                </td>
                                <td><strong>{{ $total_documents }}</strong></td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="4" style="text-align:left;">
                                    <strong>Total Cash</strong><br>
                                </td>
                                <td><strong>{{ $total_cash }}</strong></td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="4" style="text-align:left;">
                                    <strong>Total Credit</strong><br>
                                </td>
                                <td><strong>{{ $total_credit }}</strong></td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="4" style="text-align:left;">
                                    <strong>Total</strong><br>
                                </td>
                                <td><strong>{{ $grand_total }}</strong></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif
    </div>
    
</body>
</html>