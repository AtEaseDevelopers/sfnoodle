<!-- Customer Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('customer_id', __('invoices.customer')) !!}<span class="asterisk"> *</span>
    {!! Form::select('customer_id', $customerItems, null, ['class' => 'form-control select2-customer', 'placeholder' => 'Pick a Customer...']) !!}
</div>

<!-- Invoice No Field (Read Only) -->
<div class="form-group col-sm-6">
    {!! Form::label('invoice_no', __('invoice_payments.invoice_no')) !!}
    {!! Form::text('invoice_no', $invoicePayment->invoice_no ?? ($invoicePayment->invoice->invoiceno ?? 'N/A'), ['class' => 'form-control', 'readonly']) !!}
    <!-- Hidden field for invoice_id -->
    {!! Form::hidden('invoice_id', $invoicePayment->invoice_id ?? null) !!}
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', __('invoice_payments.type')) !!}<span class="asterisk"> *</span>
    {{ Form::select('type', array(1 => 'Cash'), null, ['class' => 'form-control']) }}
</div>

<!-- ChequeNo Field -->
<!-- <div class="form-group col-sm-6" id='cheque-container' style='display:none;'>
    {!! Form::label('chequeno', __('invoice_payments.cheque_no')) !!}
    {!! Form::text('chequeno', null, ['class' => 'form-control', 'maxlength' => 20]) !!}
</div> -->

<!-- Amount Field -->
<div class="form-group col-sm-6">
    {!! Form::label('amount', __('invoice_payments.amount')) !!}<span class="asterisk"> *</span>
    {!! Form::text('amount', null, ['class' => 'form-control', 'min' => 0, 'step' => 0.01]) !!}
</div>

@can('paymentapprove')
<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', __('invoice_payments.status')) !!}
    {{ Form::select('status', array(0 => 'New', 1 => 'Completed', 2 => 'Canceled'), null, ['class' => 'form-control']) }}
</div>
@endcan

<div class="form-group col-sm-12">
    <div class="row">
        <!-- Attachment Field -->
        <div class="col-sm-6">
            {!! Form::label('attachment', __('invoice_payments.attachment')) !!}
            
            <!-- Display existing attachment if it exists -->
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
            
            // Hide preview initially
            $('#new-image-preview-container').hide();
            
            if (file) {
                // Check if it's an image
                if (file.type.match('image.*')) {
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        // Show the new image preview
                        $('#new-image-preview').attr('src', e.target.result);
                        $('#new-image-preview-container').show();
                    }
                    
                    reader.readAsDataURL(file);
                } else {
                    // For PDF files, show a PDF icon
                    $('#new-image-preview').attr('src', '');
                    $('#new-image-preview-container').hide();
                }
            }
        });
        
        $("#customer_id").change(function(){
            ShowLoad();
            let customerId = $('#customer_id').val();

            // Clear the invoice dropdown first
            $('#invoice_id').empty().append('<option value="">Pick an Invoice...</option>');
            
            if (customerId === '') {
                // Disable invoice dropdown if no customer selected
                $('#invoice_id').prop('disabled', true).trigger('change');
                $('#invoice_id').select2('destroy').prop('disabled', true).select2({
                    placeholder: "First select a customer...",
                    allowClear: true,
                    width: '100%',
                    disabled: true
                });
                HideLoad();
            } else {
                // Enable invoice dropdown when customer is selected
                $('#invoice_id').prop('disabled', false);
                $('#invoice_id').select2('destroy').select2({
                    placeholder: "Search for an invoice...",
                    allowClear: true,
                    width: '100%'
                });

                var url = '{{ config("app.url") }}/invoicePayments/customer-invoices/' + customerId;

                $.get(url, function(data, status){
                    if (status === 'success') {
                        if (data.status) {
                            var options = '<option value="">Pick an Invoice...</option>';
                            if (data.data.length > 0) {
                                $.each(data.data, function(key, invoice) {
                                    options += `<option value="${invoice.id}">
                                        ${invoice.invoiceno} - RM ${invoice.total_amount.toFixed(2)} - ${invoice.date}
                                    </option>`;
                                });
                            } else {
                                options += '<option value="" disabled>No invoices found for this customer</option>';
                            }
                            $('#invoice_id').empty().append(options);
                        } else {
                            noti('e', 'Please contact your administrator', data.message);
                            $('#invoice_id').empty().append('<option value="">No invoices available</option>');
                        }
                    } else {
                        noti('e', 'Please contact your administrator', '');
                        $('#invoice_id').empty().append('<option value="">Error loading invoices</option>');
                    }
                    HideLoad();
                });
            }
        });
        
        $("#invoice_id").on("change", function(){
            getinvoice();
        });
        
        function getinvoice(){
            var invoice_id = $('#invoice_id').val();
            if(invoice_id != ''){
                ShowLoad();
                var url = '{{ config("app.url") }}/invoicePayments/getinvoice/'+invoice_id;
                $.get(url, function(data, status){
                    if(status == 'success'){
                        if(data.status){
                            var amount = 0;
                            data.data.invoicedetail.forEach((element, index, array) => {
                                amount = amount + element.totalprice;
                            });
                            $('#amount').val(amount);
                        }else{
                            noti('e','Please contact your administrator',data.message);
                        }
                        HideLoad();
                    }else{
                        noti('e','Please contact your administrator','')
                        HideLoad();
                    }
                }); 
            }
        }
        
        $('#type').change(function(){
            if($(this).val() == "5") {
                $('#cheque-container').show();
            } else {
                $('#cheque-container').hide();
            }
        });
        
        // Initialize type field to show/hide cheque container
        @if(isset($invoicePayment) && $invoicePayment->type == 5)
            $('#cheque-container').show();
        @endif
    </script>
    <style>
        /* Your existing CSS styles remain the same */
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
        
        /* Style for readonly fields */
        input[readonly], select[readonly] {
            background-color: #e9ecef;
            opacity: 1;
            cursor: not-allowed;
        }
        
        /* Ensure the form-group takes full width */
        .form-group.col-sm-12 {
            width: 100%;
        }
    </style>
@endpush