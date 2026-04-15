<!-- Invoiceno Field -->
<div class="form-group">
    {!! Form::label('invoiceno', 'Invoice No') !!}:<span class="asterisk"> *</span>
    <p>{{ $invoice->invoiceno }}</p>
</div>

<!-- Date Field -->
<div class="form-group">
    {!! Form::label('date', 'Date') !!}:<span class="asterisk"> *</span>
    <p>{{ $invoice->date ? \Carbon\Carbon::parse($invoice->date)->format('d-m-Y') : '' }}</p>
</div>

<!-- Customer Id Field -->
<div class="form-group">
    {!! Form::label('customer_id', 'Customer') !!}:<span class="asterisk"> *</span>
    <p>{{ $invoice->customer->company ?? '' }}</p>
</div>

<!-- Creator Information -->
<div class="form-group">
    {!! Form::label('created_by', 'Created By') !!}:
    <p>{{ $invoice->creator_name ?? 'System' }} ({{ $invoice->is_driver ? 'Agent' : 'User' }})</p>
</div>

<!-- Paymentterm Field -->
<div class="form-group">
    {!! Form::label('paymentterm', 'Payment Term') !!}:<span class="asterisk"> *</span>
    <p>
        {{ $invoice->payment_term_text ?? 'Unknown' }}
        @if($invoice->paymentterm == 'Cheque' && $invoice->chequeno)
            - {{ $invoice->chequeno }}
        @endif
    </p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status') !!}:<span class="asterisk"> *</span>
    <p>{{ $invoice->status_text }}</p>
</div>

<!-- Remark Field -->
<div class="form-group">
    {!! Form::label('remark', 'Remark') !!}:
    <p>{{ $invoice->remark ?? '-' }}</p>
</div>

<!-- Sales Invoice Reference -->
@if($invoice->salesInvoice)
<div class="form-group">
    {!! Form::label('sales_invoice_id', 'Sales Invoice Reference') !!}:
    <p>{{ $invoice->salesInvoice->invoiceno ?? '' }}</p>
</div>
@endif

<!-- Invoice Items Section -->
<div class="col-12 mt-4">
    <hr>
    <h4>Invoice Items</h4>
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
                    // Calculate tiered pricing and FOC items for display
                    $purchasedItems = [];
                    foreach ($invoicedetails as $detail) {
                        $purchasedItems[] = [
                            'product_id' => $detail['product_id'],
                            'quantity' => $detail['quantity'],
                            'price' => $detail['price']
                        ];
                    }
                    
                    $focItems = \App\Models\foc::calculateFocItems($invoice->customer_id, $purchasedItems, $invoice->date);
                    
                    $displayItems = [];
                    $originalTotal = 0;
                    $offerAmount = 0;
                    
                    // Process each purchased item with tiered pricing
                    foreach ($invoicedetails as $detail) {
                        $product = \App\Models\Product::find($detail['product_id']);
                        $quantity = $detail['quantity'];
                        $regularPrice = $product->price;
                        
                        // Check for special price
                        $specialPrice = \App\Models\SpecialPrice::where('product_id', $product->id)
                            ->where('customer_id', $invoice->customer_id)
                            ->where('status', 1)
                            ->first();
                        
                        $basePrice = $specialPrice ? $specialPrice->price : $regularPrice;
                        $tieredPricing = $product->tiered_pricing;
                        
                        if (!empty($tieredPricing) && is_array($tieredPricing)) {
                            // Sort tiers by quantity descending
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
                                        'display_name' => $product->name . " ({$tierQuantity} units)",
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
                                    'display_name' => $product->name,
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
                                'display_name' => $product->name,
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

<!-- Payment Information -->
@if($invoice->hasPayments())
<div class="col-12 mt-4">
    <hr>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Payment Information</h4>
        <div class="btn-group">
            @foreach($invoice->invoicePayments as $payment)
                @if($payment->attachment)
                    @php
                        $fileUrl = asset('/' . $payment->attachment);
                        $fileExtension = pathinfo($payment->attachment, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                    @endphp
                    
                    @if($isImage)
                        <button type="button" class="btn btn-info btn-sm view-attachment" 
                                data-toggle="modal" data-target="#attachmentModal"
                                data-file="{{ $fileUrl }}"
                                data-filename="{{ basename($payment->attachment) }}"
                                data-filetype="image">
                            <i class="fa fa-image"></i> View Attachment
                        </button>
                    @else
                        <a href="{{ $fileUrl }}" 
                           class="btn btn-info btn-sm" 
                           target="_blank"
                           download="{{ basename($payment->attachment) }}">
                            <i class="fa fa-download"></i> Download Attachment
                        </a>
                    @endif
                @endif
            @endforeach
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Payment Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Approved By</th>
                    <th>Approved At</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->invoicePayments as $payment)
                <tr>
                    <td>
                        @if($payment->type == \App\Models\Invoice::PAYMENT_TYPE_CASH)
                            Cash
                        @elseif($payment->type == \App\Models\Invoice::PAYMENT_TYPE_CREDIT)
                            Credit
                        @else
                            {{ $payment->type }}
                        @endif
                    </td>
                    <td>RM {{ number_format($payment->amount, 2) }}</td>
                    <td>
                        @if($payment->status == 0)
                            New
                        @elseif($payment->status == 1)
                            Completed
                        @elseif($payment->status == 2)
                            Canceled
                        @else
                            {{ $payment->status }}
                        @endif
                    </td>
                    <td>{{ $payment->approve_by ?? '-' }}</td>
                    <td>{{ $payment->approve_at ? \Carbon\Carbon::parse($payment->approve_at)->format('d-m-Y H:i:s') : '-' }}</td>
                    <td>{{ $payment->remark ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Attachment Modal -->
@if($invoice->hasPayments() && $invoice->invoicePayments->where('attachment')->isNotEmpty())
<div class="modal fade" id="attachmentModal" tabindex="-1" role="dialog" aria-labelledby="attachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachmentModalLabel">Payment Attachment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="imagePreview" style="display: none;">
                    <img id="attachmentImage" src="" alt="Attachment" class="img-fluid" style="max-height: 70vh;">
                </div>
                <div id="pdfPreview" style="display: none;">
                    <iframe id="attachmentPdf" src="" width="100%" height="600px" style="border: none;"></iframe>
                </div>
                <div id="otherFilePreview" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> This file cannot be previewed inline.
                        <br>
                        <a href="#" id="attachmentDownloadLink" class="btn btn-primary mt-2">
                            <i class="fa fa-download"></i> Download File
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="modalDownloadLink" class="btn btn-primary" download>
                    <i class="fa fa-download"></i> Download
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
    <script>
        $(document).ready(function () {
            HideLoad();
            
            // Handle escape key
            $(document).keyup(function(e) {
                if (e.key === "Escape") {
                    $('.card .card-header a')[0].click();
                }
            });
            
            // Handle attachment view buttons
            $('.view-attachment').click(function() {
                var fileUrl = $(this).data('file');
                var fileName = $(this).data('filename');
                var fileType = $(this).data('filetype');
                
                // Set download link
                $('#modalDownloadLink').attr('href', fileUrl);
                $('#modalDownloadLink').attr('download', fileName);
                $('#attachmentDownloadLink').attr('href', fileUrl);
                $('#attachmentDownloadLink').attr('download', fileName);
                
                // Hide all previews
                $('#imagePreview').hide();
                $('#pdfPreview').hide();
                $('#otherFilePreview').hide();
                
                // Show appropriate preview
                if (fileType === 'image') {
                    $('#attachmentImage').attr('src', fileUrl);
                    $('#imagePreview').show();
                } else if (fileName.toLowerCase().endsWith('.pdf')) {
                    $('#attachmentPdf').attr('src', fileUrl + '#toolbar=0');
                    $('#pdfPreview').show();
                } else {
                    $('#otherFilePreview').show();
                }
                
                // Update modal title
                $('#attachmentModalLabel').text('Attachment: ' + fileName);
            });
            
            // Clear modal content when closed
            $('#attachmentModal').on('hidden.bs.modal', function () {
                $('#attachmentImage').attr('src', '');
                $('#attachmentPdf').attr('src', '');
            });
        });
    </script>
    
    <style>
        .view-attachment:hover, 
        .view-attachment:focus {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        #attachmentModal .modal-dialog {
            max-width: 90%;
        }
        
        #attachmentModal .modal-body {
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #attachmentImage {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
        }
        
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
    </style>
@endpush