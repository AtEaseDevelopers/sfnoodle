<div class="col-12">
    <h4 class="mb-3">Sales Order Information</h4>
    @if(isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE)
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> This sales order has been converted to an invoice and cannot be edited.
        </div>
    @endif
</div>

<!-- Invoiceno Field -->
<div class="form-group col-sm-6">
    {!! Form::label('invoiceno', 'Order No') !!}<span class="asterisk"> *</span>
    {!! Form::text('invoiceno', $salesInvoice->invoiceno ?? ($nextInvoiceNumber ?? \App\Models\SalesInvoice::getNextInvoiceNumber()), [
        'class' => 'form-control',
        'maxlength' => 255, 
        'id' => 'invoiceno',
        'readonly' => isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? true : false, 
        'style' => isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? 'background-color: #e9ecef; cursor: not-allowed;' : 'background-color: #f8f9fa;'
    ]) !!}
    <small class="form-text text-muted">
         (Order No is auto-generated, but you can change it if needed.)
    </small>
</div>

<!-- Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('date', 'Date') !!}<span class="asterisk"> *</span>
    {!! Form::text('date', isset($salesInvoice->date) ? date('d-m-Y', strtotime($salesInvoice->date)) : null, [
        'class' => 'form-control',
        'id' => 'date',
        'readonly' => isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? true : false
    ]) !!}
</div>

<!-- Customer Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('customer_id', 'Customer') !!}<span class="asterisk"> *</span>
    @if(isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE)
        {!! Form::text('customer_display', optional($salesInvoice->customer)->company ?? 'N/A', [
            'class' => 'form-control',
            'readonly' => true,
            'style' => 'background-color: #e9ecef; cursor: not-allowed;'
        ]) !!}
        <input type="hidden" name="customer_id" value="{{ $salesInvoice->customer_id }}">
    @else
        {!! Form::select('customer_id', $customerItems, $salesInvoice->customer_id ?? null, [
            'class' => 'form-control select2-customer', 
            'placeholder' => 'Pick a Customer...', 
            'required',
            'disabled' => isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? true : false
        ]) !!}
    @endif
</div>

<!-- Paymentterm Field -->
<div class="form-group col-sm-6" id="paymentterm-container" style="display: none;">
    {!! Form::label('paymentterm', 'Payment Term') !!}
    <div class="form-control" id="paymentterm-display" style="background-color: #e9ecef; padding: 6px 12px; border-radius: 4px;">
        <span id="paymentterm-text">-</span>
    </div>
    <input type="hidden" name="paymentterm" id="paymentterm-input" value="{{ $salesInvoice->paymentterm ?? '' }}">
</div>

<!-- ChequeNo Field -->
<div class="form-group col-sm-6" id='cheque-container' style='display:none;'>
    {!! Form::label('chequeno', 'Cheque No') !!}
    {!! Form::text('chequeno', $salesInvoice->chequeno ?? null, [
        'class' => 'form-control',
        'maxlength' => 20,
        'readonly' => isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? true : false
    ]) !!}
</div>

<!-- Status Field - Only show in edit mode -->
@if(isset($salesInvoice) && isset($isEdit) && $isEdit)
    <!-- Only show status field in edit mode -->
    <div class="form-group col-sm-6">
        {!! Form::label('status', 'Status') !!}<span class="asterisk"> *</span>
        @if($salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE)
            {!! Form::text('status_display', \App\Models\SalesInvoice::getStatusOptions()[$salesInvoice->status] ?? 'Unknown', [
                'class' => 'form-control',
                'readonly' => true,
                'style' => 'background-color: #e9ecef; cursor: not-allowed;'
            ]) !!}
            <input type="hidden" name="status" value="{{ $salesInvoice->status }}">
        @else
            {{ Form::select('status', \App\Models\SalesInvoice::getStatusOptions(), $salesInvoice->status ?? \App\Models\SalesInvoice::STATUS_PENDING, [
                'class' => 'form-control', 
                'required',
                'disabled' => $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? true : false
            ]) }}
        @endif
    </div>
@else
    <!-- Hidden field for create mode - always set to pending -->
    <input type="hidden" name="status" value="{{ \App\Models\SalesInvoice::STATUS_PENDING }}">
@endif

<!-- Driver Field -->
<div class="form-group col-sm-6">
    {!! Form::label('driver_id', 'Agent') !!}
    @if(isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE)
        {!! Form::text('driver_display', optional($salesInvoice->driver)->name ?? 'N/A', [
            'class' => 'form-control',
            'readonly' => true,
            'style' => 'background-color: #e9ecef; cursor: not-allowed;'
        ]) !!}
        <input type="hidden" name="driver_id" value="{{ $salesInvoice->driver_id ?? '' }}">
    @else
        {!! Form::select('driver_id', $driverItems, $salesInvoice->driver_id ?? null, [
            'class' => 'form-control select2-driver', 
            'placeholder' => 'Select Agent...',
            'disabled' => isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? true : false
        ]) !!}
    @endif
</div>

<!-- Remark Field -->
<div class="form-group col-sm-6">
    {!! Form::label('remark', 'Remark') !!}
    {!! Form::text('remark', $salesInvoice->remark ?? null, [
        'class' => 'form-control',
        'maxlength' => 255,
        'readonly' => isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? true : false
    ]) !!}
</div>

<!-- Sales Invoice Details Section -->
<div class="col-12 mt-4">
    <hr>
    <h4>Order Items</h4>
    <div class="table-responsive">
        <table class="table table-bordered" id="itemsTable">
            <thead>
                <tr>
                    <th style="width: 30%;">Product <span class="asterisk">*</span></th>
                    <th style="width: 15%;">Quantity <span class="asterisk">*</span></th>
                    <th style="width: 15%;">Price <span class="asterisk">*</span></th>
                    <th style="width: 10%;">Total</th>
                    @if(!isset($salesInvoice) || $salesInvoice->status != \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE)
                        <th style="width: 5%;">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody id="itemsBody">
                @if(isset($salesInvoiceDetails) && count($salesInvoiceDetails) > 0)
                    @foreach($salesInvoiceDetails as $index => $detail)
                        <tr class="item-row">
                            <td>
                                @if(isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE)
                                    {!! Form::text("details[$index][product_display]", $productItems[$detail['product_id']] ?? 'N/A', [
                                        'class' => 'form-control',
                                        'readonly' => true,
                                        'style' => 'background-color: #e9ecef; cursor: not-allowed;'
                                    ]) !!}
                                    <input type="hidden" name="details[{{ $index }}][product_id]" value="{{ $detail['product_id'] }}">
                                @else
                                    {!! Form::select("details[$index][product_id]", $productItems, $detail['product_id'] ?? null, [
                                        'class' => 'form-control product-select', 
                                        'placeholder' => 'Select Product...', 
                                        'required',
                                        'disabled' => isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? true : false
                                    ]) !!}
                                @endif
                            </td>
                            <td>
                                {!! Form::number("details[$index][quantity]", $detail['quantity'] ?? null, [
                                    'class' => 'form-control quantity', 
                                    'min' => 1, 
                                    'step' => 1, 
                                    'required',
                                    'readonly' => isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? true : false
                                ]) !!}
                            </td>
                            <td>
                                {!! Form::number("details[$index][price]", $detail['price'] ?? null, [
                                    'class' => 'form-control price', 
                                    'min' => 0, 
                                    'step' => 0.01, 
                                    'required',
                                    'readonly' => isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? true : false
                                ]) !!}
                            </td>
                            <td>
                                <input type="text" class="form-control total" readonly 
                                    value="{{ number_format(($detail['quantity'] ?? 0) * ($detail['price'] ?? 0), 2) }}"
                                    style="{{ isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? 'background-color: #e9ecef; cursor: not-allowed;' : '' }}">
                            </td>
                            @if(!isset($salesInvoice) || $salesInvoice->status != \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE)
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row" 
                                        {{ $loop->first && count($salesInvoiceDetails) == 1 ? 'disabled' : '' }}
                                        {{ isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? 'disabled' : '' }}>
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @elseif(!isset($salesInvoice) || $salesInvoice->status != \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE)
                    <!-- Initial row for empty details -->
                    <tr class="item-row">
                        <td>
                            {!! Form::select('details[0][product_id]', $productItems, null, [
                                'class' => 'form-control product-select', 
                                'placeholder' => 'Select Product...', 
                                'required'
                            ]) !!}
                        </td>
                        <td>
                            {!! Form::number('details[0][quantity]', null, [
                                'class' => 'form-control quantity', 
                                'min' => 1, 
                                'step' => 1, 
                                'required'
                            ]) !!}
                        </td>
                        <td>
                            {!! Form::number('details[0][price]', null, [
                                'class' => 'form-control price', 
                                'min' => 0, 
                                'step' => 0.01, 
                                'required'
                            ]) !!}
                        </td>
                        <td>
                            <input type="text" class="form-control total" readonly value="0.00">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ !isset($salesInvoice) || $salesInvoice->status != \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? 3 : 4 }}" class="text-right">
                        <strong>Grand Total:</strong>
                    </td>
                    <td>
                        <input type="text" class="form-control" id="grandTotal" readonly 
                            value="{{ number_format($salesInvoice->total ?? 0, 2) }}"
                            style="{{ isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? 'background-color: #e9ecef; cursor: not-allowed;' : '' }}">
                    </td>
                    @if(!isset($salesInvoice) || $salesInvoice->status != \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE)
                        <td></td>
                    @endif
                </tr>
                @if(!isset($salesInvoice) || $salesInvoice->status != \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE)
                    <tr>
                        <td colspan="{{ !isset($salesInvoice) || $salesInvoice->status != \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? 5 : 4 }}">
                            <button type="button" class="btn btn-success btn-sm" id="addRow">
                                <i class="fa fa-plus"></i> Add Item
                            </button>
                        </td>
                    </tr>
                @endif
            </tfoot>
        </table>
    </div>
</div>

<!-- Hidden input for method -->
<input type="hidden" name="method" id="method" value="2">

<!-- Submit Field -->
<div class="form-group col-sm-12 mt-4">
    @if(isset($salesInvoice))
        @if($salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE)
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i> This sales order cannot be modified because it has been converted to an invoice.
                <a href="{{ route('salesInvoices.index') }}" class="btn btn-secondary ml-3">Back to List</a>
            </div>
        @else
            {!! Form::button('Update' , ['class' => 'btn btn-primary','id' => 'update']) !!}
            <a href="{{ route('salesInvoices.index') }}" class="btn btn-secondary">Cancel</a>
        @endif
    @else
        {!! Form::button('Create' , ['class' => 'btn btn-primary','id' => 'create']) !!}
        <a href="{{ route('salesInvoices.index') }}" class="btn btn-secondary">Cancel</a>
    @endif
</div>

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            HideLoad();
            
            // Check if we're in edit mode by checking if salesInvoice exists
            var isEditMode = {{ isset($salesInvoice) ? 'true' : 'false' }};
            var isConvertedToInvoice = {{ isset($salesInvoice) && $salesInvoice->status == \App\Models\SalesInvoice::STATUS_CONVERTED_TO_INVOICE ? 'true' : 'false' }};
            
            var productPrices = {!! json_encode($productPrices ?? []) !!};

            // If converted to invoice, disable all functionality
            if (isConvertedToInvoice) {
                // Disable all form interactions
                $('#salesInvoiceForm :input').prop('disabled', true);
                $('#salesInvoiceForm select').prop('disabled', true);
                $('#salesInvoiceForm button').prop('disabled', true);
                
                // Remove click handlers
                $('#addRow').off('click');
                $('.remove-row').off('click');
                $('#create, #update').off('click');
                
                // Hide datetime picker functionality
                $('#date').datetimepicker('destroy');
                
                return; // Stop further execution
            }
            
            // Store the current customer ID
            var currentCustomerId = $('#customer_id').val();
            
            // Initialize datetime picker (only if not converted)
            $('#date').datetimepicker({
                format: 'DD-MM-YYYY',
                useCurrent: true,
                icons: {
                    up: "icon-arrow-up-circle icons font-2xl",
                    down: "icon-arrow-down-circle icons font-2xl"
                },
                sideBySide: true
            });

            // Initialize select2 fields (only if not converted)
            $('.select2-customer').select2({
                placeholder: "Search for a customer...",
                allowClear: true,
                width: '100%'
            });

            // Initialize product select2
            $('.product-select').select2({
                placeholder: "Select product...",
                allowClear: false,
                width: '100%'
            });

            // Customer payment terms from PHP
            var customerPaymentTerms = {!! $customerPaymentTerms ?? '{}' !!};

            // Function to update product prices based on selected customer
            function updateProductPricesForCustomer(customerId) {
                if (!customerId) return;
                
                // Show loading indicator
                $('.price').each(function() {
                    $(this).val('Loading...').prop('readonly', true);
                });
                
                // AJAX call to get customer-specific prices
                $.ajax({
                    url: "{{ route('salesInvoices.getCustomerPrices', '') }}/" + customerId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Update the global productPrices variable
                            productPrices = response.product_prices;
                            
                            // Update prices for all existing product rows
                            $('.item-row').each(function() {
                                var productSelect = $(this).find('.product-select');
                                var priceField = $(this).find('.price');
                                var productId = productSelect.val();
                                
                                if (productId && productPrices[productId]) {
                                    // Only update if the price field is enabled (not manually edited)
                                    if (!priceField.data('manually-edited')) {
                                        priceField.val(productPrices[productId]);
                                        priceField.removeClass('auto-filled').addClass('customer-price');
                                        
                                        // Recalculate row total
                                        calculateRowTotal($(this));
                                    }
                                }
                            });
                            
                            // Recalculate grand total
                            calculateGrandTotal();
                            
                            // Enable price fields
                            $('.price').prop('readonly', false);
                        } else {
                            console.error('Failed to load customer prices:', response.message);
                            // Restore original prices or keep as is
                            $('.price').prop('readonly', false);
                            $('.price').each(function() {
                                if ($(this).val() === 'Loading...') {
                                    $(this).val('');
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading customer prices:', error);
                        // Restore original prices or keep as is
                        $('.price').prop('readonly', false);
                        $('.price').each(function() {
                            if ($(this).val() === 'Loading...') {
                                $(this).val('');
                            }
                        });
                    }
                });
            }

            // When customer is selected
            $("#customer_id").change(function(){
                var customerId = $(this).val();
                currentCustomerId = customerId;
                
                if(customerId && customerPaymentTerms[customerId]) {
                    updatePaymentTermDisplay(customerPaymentTerms[customerId]);
                    
                    // Update product prices for this customer
                    updateProductPricesForCustomer(customerId);
                    
                } else {
                    // Hide payment term if no customer selected or no payment term
                    $('#paymentterm-container').hide();
                    $('#cheque-container').hide();
                    
                    // Clear product prices if no customer selected
                    productPrices = {};
                }
            });

            // Function to update payment term display
            function updatePaymentTermDisplay(paymentTerm) {
                $('#paymentterm-text').text(paymentTerm);
                $('#paymentterm-input').val(paymentTerm);
                $('#paymentterm-container').show();
                
                if(paymentTerm === 'Cheque') {
                    $('#cheque-container').show();
                } else {
                    $('#cheque-container').hide();
                }
            }

            // If in edit mode and customer is already selected, ensure payment term is shown
            var customerId = $('#customer_id').val();
            if(isEditMode && customerId && customerPaymentTerms[customerId]) {
                updatePaymentTermDisplay(customerPaymentTerms[customerId]);
                // Don't update prices on initial load if editing, prices should already be set
            }

            // Function to auto-fill price when product is selected
            function autoFillPrice(selectElement) {
                var productId = selectElement.val();
                var priceField = selectElement.closest('tr').find('.price');
                
                if (productId && productPrices[productId]) {
                    // Auto-fill the price only if not manually edited
                    if (!priceField.data('manually-edited')) {
                        priceField.val(productPrices[productId]);
                        priceField.addClass('customer-price');
                        
                        // Calculate row total
                        calculateRowTotal(selectElement.closest('tr'));
                        calculateGrandTotal();
                        
                        // Highlight the field to indicate it was auto-filled
                        priceField.addClass('auto-filled');
                        setTimeout(function() {
                            priceField.removeClass('auto-filled');
                        }, 1000);
                    }
                } else {
                    // Clear price if product is deselected
                    if (!priceField.data('manually-edited')) {
                        priceField.val('');
                        priceField.removeClass('customer-price');
                    }
                }
            }

            // Track manual price edits
            $(document).on('keyup', '.price', function() {
                if ($(this).val().trim() !== '') {
                    $(this).data('manually-edited', true);
                    $(this).removeClass('customer-price');
                } else {
                    $(this).data('manually-edited', false);
                }
            });

            // Handle product selection change
            $(document).on('change', '.product-select', function() {
                autoFillPrice($(this));
            });
            
            // Add new row
            let rowCount = {{ isset($salesInvoiceDetails) && count($salesInvoiceDetails) > 0 ? count($salesInvoiceDetails) : 1 }};
            $('#addRow').click(function() {
                const newRow = `
                    <tr class="item-row">
                        <td>
                            <select name="details[${rowCount}][product_id]" class="form-control product-select" required>
                                <option value="">Select Product...</option>
                                @foreach($productItems as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="details[${rowCount}][quantity]" class="form-control quantity" min="0.01" step="0.01" required>
                        </td>
                        <td>
                            <input type="number" name="details[${rowCount}][price]" class="form-control price" min="0" step="0.01" required data-manually-edited="false">
                        </td>
                        <td>
                            <input type="text" class="form-control total" readonly value="0.00">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-row">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                
                $('#itemsBody').append(newRow);
                $('#itemsBody tr:last .product-select').select2({
                    placeholder: "Select product...",
                    allowClear: false,
                    width: '100%'
                });
                
                // Enable remove button on first row if there are multiple rows
                if (rowCount === 1) {
                    $('#itemsBody tr:first .remove-row').prop('disabled', false);
                }
                
                rowCount++;
            });

            // Remove row
            $(document).on('click', '.remove-row', function() {
                if ($('#itemsBody tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateTotals();
                    
                    // Update row numbers
                    rowCount = 0;
                    $('#itemsBody tr').each(function(index) {
                        $(this).find('select, input').each(function() {
                            const name = $(this).attr('name');
                            if (name) {
                                const newName = name.replace(/details\[\d+\]/, `details[${index}]`);
                                $(this).attr('name', newName);
                            }
                        });
                        rowCount++;
                    });
                    
                    // Disable remove button on first row if only one row left
                    if ($('#itemsBody tr').length === 1) {
                        $('#itemsBody tr:first .remove-row').prop('disabled', true);
                    }
                }
            });

            // Calculate totals
            $(document).on('keyup change', '.quantity, .price', function() {
                calculateRowTotal($(this).closest('tr'));
                calculateGrandTotal();
            });

            function calculateRowTotal(row) {
                const quantity = parseFloat(row.find('.quantity').val()) || 0;
                const price = parseFloat(row.find('.price').val()) || 0;
                const total = quantity * price;
                row.find('.total').val(total.toFixed(2));
            }

            function calculateGrandTotal() {
                let grandTotal = 0;
                $('.item-row').each(function() {
                    const total = parseFloat($(this).find('.total').val()) || 0;
                    grandTotal += total;
                });
                $('#grandTotal').val(grandTotal.toFixed(2));
            }

            // Auto-fill prices for existing items on page load
            // This is useful for when editing an existing invoice
            $('.product-select').each(function() {
                var productId = $(this).val();
                if (productId && productPrices[productId]) {
                    var priceField = $(this).closest('tr').find('.price');
                    // Only auto-fill if price field is empty or if it's not manually edited
                    if (!priceField.val() || priceField.val() == '0' || priceField.val() == '0.00' || !priceField.data('manually-edited')) {
                        priceField.val(productPrices[productId]);
                        priceField.addClass('customer-price');
                        calculateRowTotal($(this).closest('tr'));
                    }
                }
            });
            
            // Initialize manually-edited flag for existing price fields
            $('.price').each(function() {
                if ($(this).val()) {
                    // If price is already set (from database), mark it as manually edited
                    // so it won't be changed when customer changes
                    $(this).data('manually-edited', true);
                } else {
                    $(this).data('manually-edited', false);
                }
            });
            
            // Calculate initial grand total
            calculateGrandTotal();
            
            // Form validation before submit
            $('#create, #update').click(function(e) {
                e.preventDefault();
                
                // Validate at least one item with product selected
                let hasValidItem = false;
                $('.item-row').each(function() {
                    const productId = $(this).find('.product-select').val();
                    const quantity = $(this).find('.quantity').val();
                    const price = $(this).find('.price').val();
                    
                    if (productId && quantity && price) {
                        hasValidItem = true;
                    }
                });

                if (!hasValidItem) {
                    alert('Please add at least one invoice item with all required fields filled.');
                    return;
                }

                // Validate all required fields
                const form = $('#salesInvoiceForm');
                let isValid = true;
                form.find('[required]').each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (isValid) {
                    $('#method').val(2);
                    form.submit();
                } else {
                    alert('Please fill all required fields marked with *');
                }
            });

            // Initial calculation
            calculateRowTotal($('#itemsBody tr:first'));
            calculateGrandTotal();
        });
    </script>

    <style>
        .asterisk {
            color: red;
        }
        
        .select2-container--default .select2-search--dropdown .select2-search__field {
            padding: 6px 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            box-shadow: none;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff;
            color: white;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
            color: #495057;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .select2-container {
            width: 100% !important;
        }

        #paymentterm-display {
            min-height: 38px;
            display: flex;
            align-items: center;
                    }
        
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .is-invalid:focus {
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        
        /* Disabled field styles */
        .form-control:disabled,
        .form-control[readonly] {
            background-color: #e9ecef !important;
            cursor: not-allowed !important;
            opacity: 1;
        }
        
        select:disabled,
        select[readonly] {
            background-color: #e9ecef !important;
            cursor: not-allowed !important;
            opacity: 1;
        }
        
        .btn:disabled {
            cursor: not-allowed !important;
        }
        
        /* Style for auto-filled prices */
        .auto-filled {
            background-color: #e8f5e8 !important;
            border-color: #28a745 !important;
            transition: background-color 0.5s ease;
        }
        
        .customer-price {
            background-color: #f0f8ff !important;
            border-color: #17a2b8 !important;
        }
    </style>
@endpush