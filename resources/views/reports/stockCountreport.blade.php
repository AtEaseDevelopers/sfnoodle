<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Count Report</title>
    <style>
        @page {
            margin: 100px 25px;
            padding: 0;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
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
            font-size: 10px;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
            text-align: center;
            color: #2c3e50;
        }
        
        /* Main content starts after header */
        .content {
            margin-top: 5px;
        }
        
        .trip-info {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            background-color: #f9f9f9;
        }
        
        .trip-info table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        
        .trip-info td {
            padding: 8px 10px;
            border: none;
        }
        
        .trip-info .label {
            text-align: left;
            font-weight: bold;
            width: 40%;
            color: #333;
        }
        
        .trip-info .value {
            text-align: left;
            width: 60%;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
            page-break-inside: auto;
        }
        
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        thead {
            display: table-header-group;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        
        th {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
        
        .positive-diff {
            color: #28a745;
            font-weight: bold;
        }
        
        .negative-diff {
            color: #dc3545;
            font-weight: bold;
        }
        
        .zero-diff {
            color: #6c757d;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #2c3e50;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 5px;
            page-break-after: avoid;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .approval-section {
            margin-top: 30px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        
        .signature-line {
            margin-top: 40px;
            border-top: 1px solid #000;
            width: 300px;
            text-align: center;
            padding-top: 5px;
            font-size: 12px;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
            border: 1px dashed #ddd;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        
        .report-meta {
            font-size: 11px;
            color: #666;
            margin-bottom: 10px;
            text-align: right;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-left {
            text-align: left;
        }
        
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .summary-label {
            font-weight: bold;
            color: #495057;
        }
        
        .summary-value {
            font-weight: bold;
            color: #2c3e50;
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
    
    <!-- Footer that repeats on every page -->
    <div class="footer">
        Page <span class="pagenum"></span> | Generated on {{ $printed_time }}
    </div>
    
    <!-- Main Content -->
    <div class="content">
        <div class="report-title">STOCK COUNT REPORT</div>
        
        <!-- Trip Information -->
        <div class="section-title">Trip Information</div>
        <div class="trip-info">
            <table>
                <tr>
                    <td class="label">Agent:</td>
                    <td class="value">{{ $salesman }}</td>
                    <td class="label">Trip ID:</td>
                    <td class="value">{{ $trip_id }}</td>
                </tr>
                <tr>
                    <td class="label">Start Time:</td>
                    <td class="value">{{ $start_time }}</td>
                    <td class="label">End Time:</td>
                    <td class="value">{{ $end_time }}</td>
                </tr>
                <tr>
                    <td class="label">Report Printed:</td>
                    <td class="value">{{ $printed_time }}</td>
                    <td class="label">Approved By:</td>
                    <td class="value">{{ $approved_by }}</td>
                </tr>
                <tr>
                    <td class="label">Status:</td>
                    <td class="value" style="color: #28a745; font-weight: bold;">✓ Approved</td>
                    
                </tr>
            </table>
        </div>
        
        <!-- Summary Box -->
        <div class="summary-box">
            <div class="summary-item">
                <span class="summary-label">Total Products Counted:</span>
                <span class="summary-value">{{ $stock_counts->count() ?? 0 }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Expected Quantity:</span>
                <span class="summary-value">{{ number_format($total_current, 0) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Counted Quantity:</span>
                <span class="summary-value">{{ number_format($total_counted, 0) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Overall Difference:</span>
                @php
                    $diffClass = $total_difference > 0 ? 'positive-diff' : ($total_difference < 0 ? 'negative-diff' : 'zero-diff');
                @endphp
                <span class="summary-value {{ $diffClass }}">
                    {{ $total_difference > 0 ? '+' : '' }}{{ number_format($total_difference, 0) }}
                    @php
                        $variancePercent = $total_current > 0 ? ($total_difference / $total_current) * 100 : 0;
                    @endphp
                    ({{ number_format($variancePercent, 1) }}%)
                </span>
            </div>
        </div>
        
        <!-- Stock Count Details -->
        <div class="section-title">Stock Count Details</div>
        
        @if($has_data)
            <table>
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="40%">Product</th>
                        <th width="10%">Code</th>
                        <th width="15%">Expected</th>
                        <th width="15%">Counted</th>
                        <th width="15%">Difference</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $counter = 1;
                    @endphp
                    
                    @foreach($stock_counts as $item)
                        @php
                            $product = $products[$item['product_id']] ?? null;
                            $difference = $item['difference'];
                            $diffClass = $difference > 0 ? 'positive-diff' : ($difference < 0 ? 'negative-diff' : 'zero-diff');
                            $variancePercent = $item['current_quantity'] > 0 
                                ? ($difference / $item['current_quantity']) * 100 
                                : ($item['counted_quantity'] > 0 ? 100 : 0);
                        @endphp
                        
                        <tr>
                            <td>{{ $counter++ }}</td>
                            <td style="text-align: left; padding-left: 10px;">{{ $item['product_name'] }}</td>
                            <td>{{ $item['product_code'] ?? ($product->code ?? 'N/A') }}</td>
                            <td class="text-right">{{ number_format($item['current_quantity'], 0) }}</td>
                            <td class="text-right">{{ number_format($item['counted_quantity'], 0) }}</td>
                            <td class="text-right {{ $diffClass }}">
                                {{ $difference > 0 ? '+' : '' }}{{ number_format($difference, 0) }}
                                <br>
                                <small style="font-size: 10px;">({{ number_format($variancePercent, 1) }}%)</small>
                            </td>
                        </tr>
                    @endforeach
                    
                    <!-- Totals Row -->
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL:</td>
                        <td class="text-right">{{ number_format($total_current, 0) }}</td>
                        <td class="text-right">{{ number_format($total_counted, 0) }}</td>
                        <td class="text-right {{ $diffClass }}">
                            {{ $total_difference > 0 ? '+' : '' }}{{ number_format($total_difference, 0) }}
                            <br>
                            <small style="font-size: 10px;">({{ number_format($variancePercent, 1) }}%)</small>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Additional Notes Section -->
            @if($inventory_counts && $inventory_counts->count() > 0)
                <div class="section-title">Count History</div>
                <table>
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Count Date</th>
                            <th width="25%">Items Counted</th>
                            <th width="25%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $historyCounter = 1;
                        @endphp
                        @foreach($inventory_counts as $count)
                            <tr>
                                <td>{{ $historyCounter++ }}</td>
                                <td>{{ \Carbon\Carbon::parse($count->created_at)->format('d M Y h:i A') }}</td>
                                <td>{{ is_array($count->items) ? count($count->items) : 0 }} items</td>
                                <td style="text-align: left; font-size: 11px;">{{ $count->remarks ?? 'No remarks' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            
        @else
            <div class="no-data">
                <p>No approved stock count data available for this trip.</p>
                <p>The inventory count may not have been approved yet or no items were counted.</p>
            </div>
        @endif
        
        <!-- Approval Section -->
        @if($has_data)
            <div class="approval-section">
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="display: inline-block; margin: 0 50px;">
                        <div class="signature-line">Driver's Signature</div>
                        <div style="margin-top: 10px; font-size: 12px;">{{ $salesman }}</div>
                    </div>
                    <div style="display: inline-block; margin: 0 50px;">
                        <div class="signature-line">Approver's Signature</div>
                        <div style="margin-top: 10px; font-size: 12px;">{{ $approved_by }}</div>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 40px;">
                    <div style="font-size: 11px; color: #666;">
                        <p>This report has been verified and approved by authorized personnel.</p>
                        <p>Discrepancies should be reported within 24 hours of receiving this report.</p>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Footer Notes -->
        <div style="margin-top: 30px; font-size: 10px; color: #666; border-top: 1px solid #eee; padding-top: 10px;">
            <p><strong>Report Notes:</strong></p>
            <ul style="margin: 5px 0; padding-left: 15px;">
                <li>Expected Quantity: Based on system records and inventory transactions</li>
                <li>Counted Quantity: Physical count verified during stock count</li>
                <li>Difference: Positive (+) indicates surplus, Negative (-) indicates shortage</li>
                <li>This report is generated automatically by the SF Noodles System</li>
                <li>Report generated on: {{ $printed_time }}</li>
            </ul>
        </div>
    </div>
    
    <script type="text/javascript">
        // Add page numbers
        var vars = {};
        var x = document.location.search.substring(1).split('&');
        for (var i in x) {
            var z = x[i].split('=',2);
            vars[z[0]] = unescape(z[1]);
        }
        var x = ['frompage','topage','page','webpage','section','subsection','subsubsection'];
        for (var i in x) {
            var y = document.getElementsByClassName(x[i]);
            for (var j=0; j<y.length; ++j) y[j].textContent = vars[x[i]];
        }
        
        // Add page numbers to footer
        document.addEventListener('DOMContentLoaded', function() {
            var pagenum = document.getElementsByClassName('pagenum');
            for (var i = 0; i < pagenum.length; i++) {
                pagenum[i].innerHTML = "Page " + (i + 1);
            }
        });
    </script>
</body>
</html>