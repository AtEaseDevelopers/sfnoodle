<!-- Invoiceno Field -->
<div class="form-group">
    {!! Form::label('invoiceno', 'Order No') !!}:<span class="asterisk"> *</span>
    <p>{{ $salesInvoice->invoiceno }}</p>
</div>

<!-- Date Field -->
<div class="form-group">
    {!! Form::label('date', 'Date') !!}:<span class="asterisk"> *</span>
    <p>{{ $salesInvoice->date ? date('d-m-Y', strtotime($salesInvoice->date)) : '' }}</p>
</div>

<!-- Customer Id Field -->
<div class="form-group">
    {!! Form::label('customer_id', 'Customer') !!}:<span class="asterisk"> *</span>
    <p>{{ $salesInvoice->customer->company ?? '' }}</p>
</div>

<!-- Paymentterm Field -->
<div class="form-group">
    {!! Form::label('paymentterm', 'Payment Term') !!}:<span class="asterisk"> *</span>
    <p>
        {{ $salesInvoice->paymentterm ?? 'Unknown' }}
    </p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status') !!}:<span class="asterisk"> *</span>
    @php
        $statuses = [
            0 => 'Pending',
            1 => 'Cancelled',
            2 => 'Convert To Invoice'
        ];
    @endphp
    <p>{{ $statuses[$salesInvoice->status] ?? 'Unknown' }}</p>
</div>

<!-- Creator Information -->
<div class="form-group">
    {!! Form::label('created_by', 'Created By') !!}:
    <p>{{ $salesInvoice->creator->name ?? 'System' }} ({{ $salesInvoice->is_driver ? 'Agent' : 'User' }})</p>
</div>

<!-- Remark Field -->
<div class="form-group">
    {!! Form::label('remark', 'Remark') !!}:
    <p>{{ $salesInvoice->remark ?? '-' }}</p>
</div>

<!-- Sales Invoice Items Section -->
<div class="col-12 mt-4">
    <hr>
    <h4>Order Items</h4>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Get sales invoice details
                    $salesInvoiceDetails = $salesInvoice->salesInvoiceDetails;
                    $customer = $salesInvoice->customer;

                    // Prepare purchased items for FOC calculation
                    $purchasedItems = [];
                    foreach ($salesInvoiceDetails as $detail) {
                        $purchasedItems[] = [
                            'product_id' => $detail->product_id,
                            'quantity' => $detail->quantity,
                            'price' => $detail->price
                        ];
                    }
                    
                    // Calculate FOC items
                    $focItems = \App\Models\foc::calculateFocItems($salesInvoice->customer_id, $purchasedItems, $salesInvoice->date);
                    
                    $displayItems = [];
                    $originalTotal = 0;
                    $offerAmount = 0;
                    
                    // Process each purchased item with tiered pricing
                    foreach ($salesInvoiceDetails as $detail) {
                        $product = \App\Models\Product::find($detail['product_id']);
                        $quantity = $detail['quantity'];
                        $regularPrice = $product->price;
                        
                        // Get all special prices for this product
                        $specialPrices = \App\Models\SpecialPrice::where('product_id', $product->id)
                            ->where('status', 1)
                            ->get();
                        
                        $specialPrice = null;
                        
                        // First priority: Check for direct customer match
                        foreach ($specialPrices as $sp) {
                            if ($sp->customer_id == $salesInvoice->customer_id) {
                                $specialPrice = $sp;
                                break;
                            }
                        }
                        
                        // Second priority: Check for price category match
                        if (!$specialPrice && $customer && $customer->price_category) {
                            foreach ($specialPrices as $sp) {
                                if ($sp->price_category && $sp->price_category == $customer->price_category) {
                                    $specialPrice = $sp;
                                    break;
                                }
                            }
                        }
                        
                        $basePrice = $specialPrice ? $specialPrice->price : $regularPrice;
                        $hasSpecialPrice = $specialPrice ? true : false;
                        $specialPriceType = $specialPrice ? 
                            ($specialPrice->customer_id == $invoice->customer_id ? 'customer_specific' : 'category_specific') : null;
                        
                        $tieredPricing = $product->tiered_pricing;
                        
                        if (!empty($tieredPricing) && is_array($tieredPricing)) {
                            // Sort tiers by quantity descending (largest first for best value)
                            usort($tieredPricing, function($a, $b) {
                                return $b['quantity'] - $a['quantity'];
                            });
                            
                            $remainingQuantity = $quantity;
                            
                            foreach ($tieredPricing as $tier) {
                                if ($remainingQuantity <= 0) break;
                                
                                $tierQuantity = $tier['quantity'];
                                $tierPrice = $tier['price'];
                                $numberOfPackages = floor($remainingQuantity / $tierQuantity);
                                
                                if ($numberOfPackages > 0) {
                                    $quantityInTier = $numberOfPackages * $tierQuantity;
                                    $itemTotal = $numberOfPackages * $tierPrice;
                                    $regularTotalForThisTier = $quantityInTier * $basePrice;
                                    
                                    $originalTotal += $regularTotalForThisTier;
                                    $offerAmount += ($regularTotalForThisTier - $itemTotal);
                                    
                                    $displayItems[] = [
                                        'display_name' => $product->code . " ({$tierQuantity} units)",
                                        'quantity' => $numberOfPackages,
                                        'price' => $tierPrice,
                                        'totalprice' => $itemTotal,
                                        'is_foc' => false,
                                        'has_offer' => true
                                    ];
                                    
                                    $remainingQuantity -= $quantityInTier;
                                }
                            }
                            
                            // Handle remaining quantity
                            if ($remainingQuantity > 0) {
                                $itemTotal = $remainingQuantity * $basePrice;
                                $originalTotal += $itemTotal;
                                
                                $displayItems[] = [
                                    'display_name' => $product->code,
                                    'quantity' => $remainingQuantity,
                                    'price' => $basePrice,
                                    'totalprice' => $itemTotal,
                                    'is_foc' => false,
                                    'has_offer' => false
                                ];
                            }
                        } else {
                            // No tiered pricing
                            $itemTotal = $quantity * $basePrice;
                            $originalTotal += $itemTotal;
                            
                            $displayItems[] = [
                                'display_name' => $product->code,
                                'quantity' => $quantity,
                                'price' => $basePrice,
                                'totalprice' => $itemTotal,
                                'is_foc' => false,
                                'has_offer' => false
                            ];
                        }
                    }
                    
                    // Add FOC items
                    foreach ($focItems as $focItem) {
                        $displayItems[] = [
                            'display_name' => $focItem['product_name'] . " (FOC)",
                            'quantity' => $focItem['quantity'],
                            'price' => 0,
                            'totalprice' => 0,
                            'is_foc' => true,
                            'has_offer' => false
                        ];
                    }
                    
                    $finalTotal = $originalTotal - $offerAmount;
                @endphp
                
                @forelse($displayItems as $item)
                <tr @if($item['has_offer']) class="table-info" @endif>
                    <td>
                        {{ $item['display_name'] }}
                        @if($item['has_offer'])
                            <span class="badge badge-info">Volume Offer</span>
                        @endif
                        @if($item['is_foc'])
                            <span class="badge badge-success">FOC</span>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item['quantity'], 2) }}</td>
                    <td class="text-right">RM {{ number_format($item['price'], 2) }}</td>
                    <td class="text-right">RM {{ number_format($item['totalprice'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No items found</td>
                </tr>
                @endforelse
            </tbody>
            @if($offerAmount > 0)
            <tfoot>
                <tr class="table-light">
                    <td colspan="3" class="text-right"><strong>Original Total:</strong></td>
                    <td class="text-right"><strong>RM {{ number_format($originalTotal, 2) }}</strong></td>
                </tr>
                <tr class="table-success">
                    <td colspan="3" class="text-right"><strong>Volume Offer Discount:</strong></td>
                    <td class="text-right"><strong>- RM {{ number_format($offerAmount, 2) }}</strong></td>
                </tr>
                <tr class="table-active">
                    <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
                    <td class="text-right"><strong>RM {{ number_format($finalTotal, 2) }}</strong></td>
                </tr>
            </tfoot>
            @else
            <tfoot>
                <tr class="table-active">
                    <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
                    <td class="text-right"><strong>RM {{ number_format($finalTotal, 2) }}</strong></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<!-- Converted Invoice Reference -->
@if($salesInvoice->status == 2 && $salesInvoice->invoice)
<div class="col-12 mt-4">
    <div class="alert alert-info">
        <strong><i class="fa fa-info-circle"></i> Converted to Invoice:</strong>
        <a href="{{ route('invoices.show', encrypt($salesInvoice->invoice->id)) }}" class="alert-link">
            {{ $salesInvoice->invoice->invoiceno }}
        </a>
    </div>
</div>
@endif

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('.card .card-header a')[0].click();
            }
        });
        $(document).ready(function () {
            HideLoad();
        });
    </script>
    
    <style>
        .table-info {
            background-color: #e3f2fd;
        }
        
        .badge-info {
            background-color: #17a2b8;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            margin-left: 8px;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            margin-left: 8px;
        }
        
        .table-light {
            background-color: #f8f9fa;
        }
        
        .table-success {
            background-color: #d4edda;
        }
        
        .table-active {
            background-color: #e9ecef;
        }
    </style>
@endpush