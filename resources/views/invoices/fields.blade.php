<div class="col-12">
    <h4 class="mb-3">Invoice Information</h4>
</div>
<!-- Invoiceno Field -->
<div class="form-group col-sm-6">
    {!! Form::label('invoiceno', 'Invoice No') !!}<span class="asterisk"> *</span>
    {!! Form::text('invoiceno', $invoice->invoiceno ?? ($nextInvoiceNumber ?? \App\Models\Invoice::getNextInvoiceNumber()), [
        'class' => 'form-control',
        'maxlength' => 255, 
        'id' => 'invoiceno',
        'readonly' => false, 
        'style' => 'background-color: #f8f9fa;'
    ]) !!}
    <small class="form-text text-muted">
         (Invoice No is auto-generated, but you can change it if needed.)
    </small>
</div>

<!-- Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('date', 'Date') !!}<span class="asterisk"> *</span>
    {!! Form::text('date', null, ['class' => 'form-control','id'=>'date']) !!}
</div>

<!-- Customer Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('customer_id', 'Customer') !!}<span class="asterisk"> *</span>
    {!! Form::select('customer_id', $customerItems, null, ['class' => 'form-control select2-customer', 'placeholder' => 'Pick a Customer...', 'required']) !!}
</div>

<!-- Paymentterm Field -->
<div class="form-group col-sm-6" id="paymentterm-container" style="display: none;">
    {!! Form::label('paymentterm', 'Payment Term') !!}
    <div class="form-control" id="paymentterm-display" style="background-color: #e9ecef; padding: 6px 12px; border-radius: 4px;">
        <span id="paymentterm-text">-</span>
    </div>
    <input type="hidden" name="paymentterm" id="paymentterm-input">
</div>

<!-- ChequeNo Field -->
<div class="form-group col-sm-6" id='cheque-container' style='display:none;'>
    {!! Form::label('chequeno', 'Cheque No') !!}
    {!! Form::text('chequeno', null, ['class' => 'form-control','maxlength' => 20]) !!}
</div>

<!-- Status Field -->
@if(isset($invoice))
    <!-- Show status field in edit mode -->
    <div class="form-group col-sm-6">
        {!! Form::label('status', 'Status') !!}<span class="asterisk"> *</span>
        {{ Form::select('status', \App\Models\Invoice::getStatusOptions(), $invoice->status ?? \App\Models\Invoice::STATUS_COMPLETED, ['class' => 'form-control', 'required', 'id' => 'status']) }}
    </div>
@else
    <!-- Hidden field for create mode - always set to completed -->
    <input type="hidden" name="status" id="status" value="{{ \App\Models\Invoice::STATUS_COMPLETED }}">
@endif

<!-- Remark Field -->
<div class="form-group col-sm-6">
    {!! Form::label('remark', 'Remark') !!}
    {!! Form::text('remark', null, ['class' => 'form-control','maxlength' => 255]) !!}
</div>

<!-- Invoice Details Section -->
<div class="col-12 mt-4">
    <hr>
    <h4>Invoice Items</h4>
    <div class="table-responsive">
        <table class="table table-bordered" id="itemsTable">
            <thead>
                <tr>
                    <th style="width: 30%;">Product <span class="asterisk">*</span></th>
                    <th style="width: 15%;">Quantity <span class="asterisk">*</span></th>
                    <th style="width: 15%;">Price <span class="asterisk">*</span></th>
                    <th style="width: 10%;">Total</th>
                    <th style="width: 5%;">Action</th>
                </tr>
            </thead>
            <tbody id="itemsBody">

            @if(isset($invoiceDetails) && count($invoiceDetails) > 0)
                @foreach($invoiceDetails as $index => $detail)
                <tr class="item-row">
                    <td>
                        {!! Form::select("details[$index][product_id]", $productItems, $detail['product_id']??null , ['class' => 'form-control product-select', 'placeholder' => 'Select Product...', 'required']) !!}
                    </td>
                    <td>
                        {!! Form::number("details[$index][quantity]", $detail['quantity']??null, ['class' => 'form-control quantity', 'min' => 0.01, 'step' => 0.01, 'required']) !!}
                    </td>
                    <td>
                        {!! Form::number("details[$index][price]", $detail['price']??null , ['class' => 'form-control price', 'min' => 0, 'step' => 0.01, 'required']) !!}
                    </td>
               
                    <td>
                        <input type="text" class="form-control total" readonly value="{{ number_format(($detail['quantity']??0) * ($detail['price']??0), 2) }}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-row" {{ $loop->first && count($invoiceDetails) == 1 ? 'disabled' : '' }}>
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            @else
                <!-- Initial row -->
                <tr class="item-row">
                    <td>
                        {!! Form::select('details[0][product_id]', $productItems, null, ['class' => 'form-control product-select', 'placeholder' => 'Select Product...', 'required']) !!}
                    </td>
                    <td>
                        {!! Form::number('details[0][quantity]', null, ['class' => 'form-control quantity', 'min' => 0.01, 'step' => 0.01, 'required']) !!}
                    </td>
                    <td>
                        {!! Form::number('details[0][price]', null, ['class' => 'form-control price', 'min' => 0, 'step' => 0.01, 'required']) !!}
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
                    <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
                    <td>
                        <input type="text" class="form-control" id="grandTotal" readonly value="0.00">
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="6">
                        <button type="button" class="btn btn-success btn-sm" id="addRow">
                            <i class="fa fa-plus"></i> Add Item
                        </button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>


<!-- Invoice Payment Section (Only show when status is Completed AND payment term is Cash) -->
<div id="invoice-payment-section" class="col-12 mt-4" style="display: none;">
    <hr>
    <h4 class="mb-3">Payment Information (Cash Payment)</h4>
    
    @if(isset($invoicePayment) && $invoicePayment)
    <div class="alert alert-success">
        <i class="fa fa-check-circle"></i> Payment record already exists for this invoice.
    </div>
    @else
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> Payment record will be automatically created when invoice status is set to "Completed" with Cash payment term.
    </div>
    @endif
    
    <div class="row">
        <div class="form-group col-sm-6">
            {!! Form::label('payment_amount_display', 'Payment Amount') !!}
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">RM</span>
                </div>
                <input type="text" class="form-control" id="payment_amount" readonly 
                       value="{{ isset($invoicePayment) && $invoicePayment ? number_format($invoicePayment->amount, 2) : '0.00' }}">
            </div>
            <small class="form-text text-muted">Auto-calculated from invoice items</small>
        </div>
        
        <!-- Attachment Field -->
        <div class="form-group col-sm-6">
            {!! Form::label('payment_attachment', 'Payment Receipt/Attachment') !!}
            @if(!isset($invoicePayment) || !$invoicePayment)
            <span class="asterisk"> *</span>
            @endif
            <div class="custom-file">
                <input type="file" class="custom-file-input" name="payment_attachment" id="payment_attachment" accept=".jpg, .jpeg, .png, .pdf">
                <label id="payment_attachment-label" class="custom-file-label" for="payment_attachment">
                    Choose file
                </label>
            </div>
            <small class="form-text text-muted">
                Accept .jpg, .jpeg, .png, .pdf (Max: 2MB)
                @if(isset($invoicePayment) && $invoicePayment)
                <br>Leave empty to keep existing attachment.
                @endif
            </small>
        </div>

        <!-- Payment Remark Field -->
        <div class="form-group col-sm-6">
            {!! Form::label('payment_remark', 'Payment Remark') !!}
            {!! Form::text('payment_remark', isset($invoicePayment) ? $invoicePayment->remark : null, [
                'class' => 'form-control', 
                'maxlength' => 255, 
                'placeholder' => 'Optional payment note'
            ]) !!}
        </div>
    </div>
</div>

<!-- Hidden input for method -->
<input type="hidden" name="method" id="method" value="2">

<!-- Submit Field -->
<div class="form-group col-sm-12 mt-4">
    @if(isset($invoice))
        {!! Form::button('Update' , ['class' => 'btn btn-primary','id' => 'update']) !!}
        <!-- Cancel Invoice button - only show if invoice is not already cancelled -->
        @if($invoice->status != \App\Models\Invoice::STATUS_CANCELLED)
            <button type="button" class="btn btn-danger" id="cancelInvoiceBtn">
                <i class="fa fa-ban"></i> Cancel Invoice
            </button>
        @endif

    @else
        {!! Form::button('Create' , ['class' => 'btn btn-primary','id' => 'create']) !!}
    @endif
    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">Cancel</a>
</div>
<!-- Cancel Invoice Form (only in edit mode when not cancelled) -->
@if(isset($invoice) && $invoice->status != \App\Models\Invoice::STATUS_CANCELLED)
    <form id="cancelInvoiceForm" method="POST" action="{{ route('invoices.cancelInvoice', $invoice->id) }}" style="display: none;">
        @csrf
        @method('PUT')
    </form>
@endif

@push('scripts')
    <!-- Include SweetAlert2 from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script type="text/javascript">
    $(document).ready(function () {
        HideLoad();

        var isEditMode = {{ isset($invoice) ? 'true' : 'false' }};
        if (isEditMode) {

            // Cancel Invoice Confirmation with SweetAlert
            $('#cancelInvoiceBtn').click(function(e) {
                e.preventDefault();
                            
                // Build confirmation message
                var message = '<div class="text-left">';
                message += '<p>You are about to cancel this invoice. This action cannot be undone.</p>';
                message += '<div class="alert alert-danger" style="font-size: 14px;">';
                message += '<strong>Effects of cancellation:</strong>';
                message += '<ul class="mb-0 pl-3">';
                message += '<li>Invoice status will be changed to "Cancelled"</li>';
                
                @if(isset($invoice) && $invoice->paymentterm == 'Cash')
                message += '<li>Associated payment records will be cancelled</li>';
                @endif
                
                @if(isset($invoice) && $invoice->is_driver)
                message += '<li>Inventory balance will be restored to driver: <strong>{{ $invoice->creator->name ?? "Unknown Driver" }}</strong></li>';
                @endif
                
                message += '</ul>';
                message += '</div>';
                message += '<p>Please confirm to proceed with cancellation.</p>';
                message += '</div>';
                
                // Show confirmation dialog
                Swal.fire({
                    title: 'Are you sure?',
                    html: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, cancel invoice!',
                    cancelButtonText: 'No, keep it',
                    width: '600px',
                    customClass: {
                        popup: 'text-left',
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        
                        // Show loading
                        Swal.fire({
                            title: 'Processing...',
                            text: 'Cancelling invoice...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            willOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Submit the form immediately (remove setTimeout)
                        $('#cancelInvoiceForm').submit();
                    } else {
                        console.log('User cancelled the action');
                    }
                });
            });
        

            // Fallback function if SweetAlert fails
            function confirmCancelInvoice() {
                var message = "Are you sure you want to cancel this invoice?\n\n";
                message += "Effects:\n";
                message += "- Invoice status will be changed to 'Cancelled'\n";
                
                @if(isset($invoice) && $invoice->paymentterm == 'Cash')
                message += "- Associated payment records will be cancelled\n";
                @endif
                
                @if(isset($invoice) && $invoice->is_driver)
                message += "- Inventory balance will be restored to driver\n";
                @endif
                
                message += "\nThis action cannot be undone.";
                
                return confirm(message);
            }
        }
        // Check if we're in edit mode by checking if invoice exists
        
        // Get payment information from PHP if in edit mode
        var invoicePayment = {!! isset($invoicePayment) ? json_encode($invoicePayment) : 'null' !!};
        var invoiceStatus = '{{ isset($invoice) ? $invoice->status : "" }}';
        var invoicePaymentTerm = '{{ isset($invoice) ? $invoice->paymentterm : "" }}';
        var attachmentPath = invoicePayment && invoicePayment.attachment ? '{{ isset($invoicePayment) && $invoicePayment->attachment ? $invoicePayment->attachment : "" }}' : '';
                
        var productPrices = {!! json_encode($productPrices ?? []) !!};

        // Remove extra quotes from attachmentPath if they exist
        if (attachmentPath && attachmentPath.startsWith('"') && attachmentPath.endsWith('"')) {
            attachmentPath = attachmentPath.substring(1, attachmentPath.length - 1);
        }
        
        // Update payment amount when invoice items change
        $(document).on('keyup change', '.quantity, .price', function() {
            if ($('#status').val() == '{{ \App\Models\Invoice::STATUS_COMPLETED }}') {
                updatePaymentAmount();
            }
        });

        // Handle file input label
        $("#payment_attachment").on("change", function(){
            if(this.value != ''){
                $('#payment_attachment-label').html(this.value);
            }else{
                $('#payment_attachment-label').html('Choose file');
            }
        });

        // Initialize datetime picker
        $('#date').datetimepicker({
            format: 'DD-MM-YYYY',
            useCurrent: true,
            icons: {
                up: "icon-arrow-up-circle icons font-2xl",
                down: "icon-arrow-down-circle icons font-2xl"
            },
            sideBySide: true
        });

        // Initialize select2 fields
        $('.select2-customer').select2({
            placeholder: "Search for a customer...",
            allowClear: true,
            width: '100%'
        });

        $('.select2-driver').select2({
            placeholder: "Search for a driver...",
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

        // When customer is selected
        $("#customer_id").change(function(){
            var customerId = $(this).val();
            if(customerId && customerPaymentTerms[customerId]) {
                updatePaymentTermDisplay(customerPaymentTerms[customerId]);
            } else {
                // Hide payment term if no customer selected or no payment term
                $('#paymentterm-container').hide();
                $('#cheque-container').hide();
                $('#paymentterm-input').val('');
            }
            
            // Always update payment section visibility after customer change
            setTimeout(updatePaymentSectionVisibility, 100);
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
            
            // Update payment section visibility when payment term changes
            updatePaymentSectionVisibility();
        }

        // Function to update payment amount
        function updatePaymentAmount() {
            let grandTotal = parseFloat($('#grandTotal').val()) || 0;
            $('#payment_amount').val(grandTotal.toFixed(2));
        }

        // Function to update payment section visibility
        function updatePaymentSectionVisibility() {
            var status = $('#status').val();
            var paymentTerm = $('#paymentterm-input').val();
                        
            // Show payment section if status is COMPLETED AND payment term is Cash
            if (status == '{{ \App\Models\Invoice::STATUS_COMPLETED }}' && paymentTerm == 'Cash') {
                $('#invoice-payment-section').slideDown();
                
                // In edit mode, show existing attachment if exists
                if (isEditMode && attachmentPath) {
                    showExistingAttachment();
                }
            } else {
                $('#invoice-payment-section').slideUp();
            }
        }

        // Function to display existing attachment in edit mode
        function showExistingAttachment() {
            if (isEditMode && attachmentPath && attachmentPath !== '') {
                
                // Check if existing attachment section already exists
                if ($('#existing-attachment-section').length === 0) {
                        var cleanPath = attachmentPath.replace(/^storage\//, '');
                                
                        // Create the full URL
                        var attachmentUrl = "{{ url('/') }}/" + cleanPath;                    
                        var fileName = getFileNameFromPath(attachmentPath);
                    
                    var existingAttachmentHtml = `
                        <div class="form-group col-sm-12" id="existing-attachment-section">
                            <label>Existing Attachment:</label>
                            <div class="alert alert-light border">
                                <div class="d-flex align-items-center">
                                    <div class="btn-group">
                                        <a href="${attachmentUrl}" 
                                            class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                        <a href="${attachmentUrl}" 
                                            class="btn btn-sm btn-outline-success" download>
                                            <i class="fa fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Upload a new file to replace this attachment</small>
                        </div>
                    `;
                    
                    // Insert after attachment field's parent div
                    $('#payment_attachment').closest('.form-group').after(existingAttachmentHtml);
                }
            }
        }

        function getFileNameFromPath(path) {
            return path.split('/').pop();
        }

        // If in edit mode and invoice data exists, populate fields
        if(isEditMode) {
            var invoice = {!! isset($invoice) ? json_encode($invoice) : 'null' !!};
            
            if(invoice) {
                // Set payment term if exists
                if(invoice.paymentterm) {
                    updatePaymentTermDisplay(invoice.paymentterm);
                }
                
                // If invoice has customer data, check for payment term
                if(invoice.customer_id && customerPaymentTerms[invoice.customer_id]) {
                    updatePaymentTermDisplay(customerPaymentTerms[invoice.customer_id]);
                }
                
                // Show cheque field if payment term is Cheque
                if(invoice.paymentterm === 'Cheque') {
                    $('#cheque-container').show();
                }
                
                // Pre-fill payment remark if exists
                if(invoicePayment && invoicePayment.remark) {
                    $('input[name="payment_remark"]').val(invoicePayment.remark);
                }
                
                // Update payment amount from invoice
                updatePaymentAmount();
            }
            
            // Update payment section visibility on edit mode load
            setTimeout(function() {
                updatePaymentSectionVisibility();
                if (attachmentPath) {
                    showExistingAttachment();
                }
            }, 500);
        }

        // Status change handler
        $('#status').change(function() {
            updatePaymentSectionVisibility();
        });

        // Function to auto-fill price when product is selected
        function autoFillPrice(selectElement) {
            var productId = selectElement.val();
            var priceField = selectElement.closest('tr').find('.price');
            
            if (productId && productPrices[productId]) {
                // Auto-fill the price
                priceField.val(productPrices[productId]);
                
                // Calculate row total
                calculateRowTotal(selectElement.closest('tr'));
                calculateGrandTotal();
                
                // Highlight the field to indicate it was auto-filled
                priceField.addClass('auto-filled');
                setTimeout(function() {
                    priceField.removeClass('auto-filled');
                }, 1000);
            }
        }

        // Handle product selection change
        $(document).on('change', '.product-select', function() {
            autoFillPrice($(this));
        });

        // Add new row
        let rowCount = {{ isset($invoiceDetails) && count($invoiceDetails) > 0 ? count($invoiceDetails) : 1 }};
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
                        <input type="number" name="details[${rowCount}][price]" class="form-control price" min="0" step="0.01" required>
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
            if ($('#itemsBody tr').length > 1) {
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
                updateRowNumbers();
                
                // Disable remove button on first row if only one row left
                if ($('#itemsBody tr').length === 1) {
                    $('#itemsBody tr:first .remove-row').prop('disabled', true);
                }
            }
        });

        // Function to update row numbers
        function updateRowNumbers() {
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
        }

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
                // Only auto-fill if price field is empty
                if (!priceField.val() || priceField.val() == '0' || priceField.val() == '0.00') {
                    priceField.val(productPrices[productId]);
                    calculateRowTotal($(this).closest('tr'));
                }
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

            // Get current status and payment term
            const status = $('#status').val();
            const paymentTerm = $('#paymentterm-input').val();
            
            // ATTACHMENT IS NOW OPTIONAL - NO VALIDATION REQUIRED
            // Remove any invalid classes that might have been added
            $('#payment_attachment').removeClass('is-invalid');
            $('#payment_attachment').closest('.form-group').find('.custom-file-label').removeClass('text-danger');

            // Validate all required fields
            const form = $('#invoiceForm'); // Make sure your form has id="invoiceForm"
            let isValid = true;
            let firstInvalidField = null;
            
            form.find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                    if (!firstInvalidField) {
                        firstInvalidField = $(this);
                    }
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                alert('Please fill all required fields marked with *');
                if (firstInvalidField) {
                    firstInvalidField.focus();
                }
                return;
            }

            // Show loading indicator
            ShowLoad();
            
            $('#method').val(2);
            form.submit();
        });
        
        // Initial calculation
        $('.item-row').each(function() {
            calculateRowTotal($(this));
        });
        calculateGrandTotal();
        
        // Initial check for status on page load
        if($('#status').val() == '{{ \App\Models\Invoice::STATUS_COMPLETED }}') {
            updatePaymentAmount();
            updatePaymentSectionVisibility();
        }
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
            cursor: not-allowed;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .is-invalid:focus {
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        .custom-file-label.text-danger {
            color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        .custom-file-input.is-invalid ~ .custom-file-label {
            border-color: #dc3545 !important;
        }

        .custom-file-input.is-invalid:focus ~ .custom-file-label {
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        /* Optional: Add a visual indicator for required attachment */
        #invoice-payment-section .custom-file-label::after {
            content: " *";
            color: red;
        }
        
        /* SweetAlert custom styles */
        .swal2-popup {
            font-family: inherit;
        }
        
        .swal2-confirm {
            margin-right: 10px;
        }
    </style>
@endpush