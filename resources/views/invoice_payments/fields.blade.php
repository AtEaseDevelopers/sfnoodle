<!-- Customer Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('customer_id', __('invoices.customer')) !!}<span class="asterisk"> *</span>
    {!! Form::select('customer_id', $customerItems, null, ['class' => 'form-control select2-customer', 'placeholder' => 'Pick a Customer...']) !!}
</div>

<!-- Invoice Id Field - MULTIPLE SELECTION -->
<div class="form-group col-sm-6">
    {!! Form::label('invoice_id', __('invoice_payments.invoice_no')) !!}<span class="asterisk"> *</span>
    <select name="invoice_id[]" id="invoice_id" class="form-control select2-invoice" multiple data-live-search="true" {{ isset($invoicePayment) ? 'disabled' : '' }}>
        <option disabled>Pick Invoices...</option>
        @if(isset($invoices))
            @foreach($invoices as $invoice)
                <option value="{{ $invoice->id }}" 
                    {{ (isset($selectedInvoices) && in_array($invoice->id, $selectedInvoices)) ? 'selected' : '' }}>
                    {{ $invoice->invoiceno }} - RM {{ number_format($invoice->total_amount ?? 0, 2) }} - {{ $invoice->date }}
                </option>
            @endforeach
        @endif
    </select>
    
    <!-- Hidden fields for selected invoices in edit mode -->
    @if(isset($invoicePayment) && isset($selectedInvoices))
        @foreach($selectedInvoices as $selectedInvoice)
            <input type="hidden" name="invoice_id[]" value="{{ $selectedInvoice }}">
        @endforeach
    @endif
</div>
<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', __('invoice_payments.type')) !!}<span class="asterisk"> *</span>
    {{ Form::select('type', array(1 => 'Cash', 3 => 'Online BankIn', 4 => 'E-wallet', 5 => 'Cheque'), null, ['class' => 'form-control']) }}
</div>

<!-- ChequeNo Field -->
<div class="form-group col-sm-6" id='cheque-container' style='display:none;'>
    {!! Form::label('chequeno', __('invoice_payments.cheque_no')) !!}
    {!! Form::text('chequeno', null, ['class' => 'form-control', 'maxlength' => 20]) !!}
</div>

<!-- Amount Field -->
<div class="form-group col-sm-6">
    {!! Form::label('amount', __('invoice_payments.amount')) !!}<span class="asterisk"> *</span>
    {!! Form::text('amount', null, ['class' => 'form-control', 'min' => 0, 'step' => 0.01, 'readonly' => true]) !!}
</div>

@can('paymentapprove')
@if(isset($invoicePayment))
<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', __('invoice_payments.status')) !!}
    {{ Form::select('status', array(0 => 'New', 1 => 'Completed', 2 => 'Cancelled'), null, ['class' => 'form-control']) }}
</div>
@endif
@endcan

<div class="form-group col-sm-12">
    <div class="row">
        <!-- Attachment Field -->
        <div class="col-sm-6">
            {!! Form::label('attachment', __('invoice_payments.attachment')) !!}
            
            @if(isset($invoicePayment) && $invoicePayment->attachment)
                <div class="mb-2">
                    <p class="mb-1"><strong>Current Attachment:</strong></p>
                    @php
                        $attachmentPath = $invoicePayment->attachment;
                        $isImage = in_array(pathinfo($attachmentPath, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']);
                    @endphp
                    
                    @if($isImage)
                        <div class="mb-2" id="current-attachment-container">
                            <img src="{{ asset('/' . $attachmentPath) }}" 
                                 alt="Payment Attachment" 
                                 class="img-thumbnail" 
                                 style="max-width: 200px; max-height: 200px;">
                        </div>
                    @else
                        <div class="mb-2">
                            <a href="{{ asset('/' . $attachmentPath) }}" 
                               target="_blank" 
                               class="btn btn-info btn-sm">
                                <i class="fa fa-file"></i> View PDF/Document
                            </a>
                        </div>
                    @endif
                </div>
            @endif
            
            <div class="custom-file">
                <input type="file" class="custom-file-input" name="attachment" id="attachment" 
                       accept=".jpg, .jpeg, .png, .pdf, .gif">
                <label id="attachment-label" class="custom-file-label" for="attachment">
                    @if(isset($invoicePayment) && $invoicePayment->attachment)
                        Replace file (Current: {{ basename($invoicePayment->attachment) }})
                    @else
                        Choose file
                    @endif
                </label>
            </div>
            <small class="form-text text-muted">
                Accept .jpg, .jpeg, .png, .gif, .pdf (Max: 5MB)
            </small>
        </div>
        
        <!-- New Image Preview Column -->
        <div class="col-sm-6">
            <div id="new-image-preview-container" style="display: none;">
                <p class="mb-1"><strong>New Attachment Preview:</strong></p>
                <img id="new-image-preview" src="" alt="Preview" 
                     class="img-thumbnail" 
                     style="max-width: 200px; max-height: 200px;">
            </div>
        </div>
    </div>
</div>

<!-- Remark Field -->
<div class="form-group col-sm-6">
    {!! Form::label('remark', __('invoice_payments.remark')) !!}
    {!! Form::text('remark', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit(__('invoice_payments.save'), ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('invoicePayments.index') }}" class="btn btn-secondary">{{ __('invoice_payments.cancel') }}</a>
</div>

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('form a.btn-secondary')[0].click();
            }
        });
        
        $(document).ready(function () {
            // Initialize select2 for customer
            $('.select2-customer').select2({
                placeholder: "Search for a customer...",
                allowClear: true,
                width: '100%'
            });
            
            // Initialize select2 for invoice with multiple selection
            $('.select2-invoice').select2({
                allowClear: true,
                width: '100%'
            });
            
            var isEditMode = @json(isset($invoicePayment));
            
            if (isEditMode) {
                // In edit mode, disable the customer and invoice fields
                $('#customer_id').prop('disabled', true);
                $('#invoice_id').prop('disabled', true);
                $('.select2-customer').prop('disabled', true);
                $('.select2-invoice').prop('disabled', true);
                
                // Show cheque container if cheque type is selected
                if ($('#type').val() == "5") {
                    $('#cheque-container').show();
                }
            }
            
            HideLoad();
        });
        
        $("#attachment").on("change", function(){
            if(this.value != ''){
                $('#attachment-label').html(this.value);
            } else {
                @if(isset($invoicePayment) && $invoicePayment->attachment)
                    $('#attachment-label').html('Replace file (Current: {{ basename($invoicePayment->attachment) }})');
                @else
                    $('#attachment-label').html('Choose file');
                @endif
            }
        });

        // Preview image before upload
        $("#attachment").on("change", function(e) {
            var file = e.target.files[0];
            
            $('#new-image-preview-container').hide();
            
            if (file) {
                if (file.type.match('image.*')) {
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        $('#new-image-preview').attr('src', e.target.result);
                        $('#new-image-preview-container').show();
                    }
                    
                    reader.readAsDataURL(file);
                } else {
                    $('#new-image-preview').attr('src', '');
                    $('#new-image-preview-container').hide();
                }
            }
        });
        
        // Only run these functions in create mode
        @if(!isset($invoicePayment))
        $("#invoice_id").on("change", function(){
            getInvoicesTotal();
        });

        $("#customer_id").change(function(){
            ShowLoad();
            let customerId = $('#customer_id').val();

            if (customerId === '') {
                var o = '<option disabled>Pick Invoices...</option>';
                $('select[name="invoice_id[]"]').html(o);
                $('.select2-invoice').select2('destroy').empty().select2({
                    placeholder: "First select a customer...",
                    allowClear: true,
                    width: '100%'
                });
                $('#amount').val('');
                HideLoad();
            } else {
                var url = '{{ config("app.url") }}/invoicePayments/customer-invoices/' + customerId;
                $.get(url, function(data, status){
                    if (status === 'success') {
                        if (data.status) {
                            var o = '<option disabled>Pick Invoices...</option>';
                            if (data.data.length > 0) {
                                $.each(data.data, function(key, invoice) {
                                    o += `<option value="${invoice.id}">
                                            ${invoice.invoiceno} - RM ${parseFloat(invoice.total_amount).toFixed(2)} - ${invoice.date}
                                        </option>`;
                                });
                            } else {
                                o += '<option value="" disabled>No invoices found for this customer</option>';
                            }
                            
                            $('select[name="invoice_id[]"]').html(o);
                            $('.select2-invoice').select2('destroy').select2({
                                allowClear: true,
                                width: '100%'
                            });
                        } else {
                            noti('e', 'Please contact your administrator', data.message);
                        }
                    } else {
                        noti('e', 'Please contact your administrator', '');
                    }
                    HideLoad();
                });
            }
        });

        function getInvoicesTotal(){
            var invoice_ids = $('#invoice_id').val();
            if(invoice_ids && invoice_ids.length > 0){
                ShowLoad();
                var url = '{{ config("app.url") }}/invoicePayments/getinvoices';
                var params = $.param({invoice_ids: invoice_ids});
                $.get(url + '?' + params, function(data, status){
                    if(status == 'success'){
                        if(data.status){
                            var totalAmount = 0;
                            data.data.forEach((invoice) => {
                                // Use the pre-calculated discounted total from backend
                                totalAmount += parseFloat(invoice.total_amount);
                            });
                            $('#amount').val(totalAmount.toFixed(2));
                        }else{
                            noti('e','Please contact your administrator',data.message);
                        }
                        HideLoad();
                    }else{
                        noti('e','Please contact your administrator','')
                        HideLoad();
                    }
                });
            } else {
                $('#amount').val('');
            }
        }
        @endif
        
        $('#type').change(function(){
            if($(this).val() == "5") {
                $('#cheque-container').show();
            } else {
                $('#cheque-container').hide();
            }
        });
    </script>
    <style>
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
        
        /* Multiple select styling */
        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            padding: 0 6px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 4px 8px;
            margin-top: 4px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 6px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #ffc107;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--open .select2-selection--multiple {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .select2-container {
            width: 100% !important;
        }
        
        input[readonly], select[readonly] {
            background-color: #e9ecef;
            opacity: 1;
            cursor: not-allowed;
        }
        
        .form-group.col-sm-12 {
            width: 100%;
        }
    </style>
@endpush