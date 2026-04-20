<!-- Product Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('product_id', __('focs.product')) !!}<span class="asterisk"> *</span>
    <select name="product_id" id="product_id" class="form-control select2-product" style="width: 100%;">
        <option value="">Pick a Product...</option>
        @foreach($productData as $product)
            <option value="{{ $product['id'] }}" data-code="{{ $product['code'] }}" {{ (isset($foc) && $foc->product_id == $product['id']) ? 'selected' : '' }}>
                {{ $product['name'] }} ({{ $product['code'] }})
            </option>
        @endforeach
    </select>
</div>

<!-- Customer Field - Different for Create and Edit -->
@if(isset($foc) && $foc->id)
    {{-- EDIT MODE: Single Customer Selection --}}
    <div class="form-group col-sm-6">
        {!! Form::label('customer_id', __('invoices.customer')) !!}<span class="asterisk"> *</span>
        {!! Form::select('customer_id', $customerItems, null, ['class' => 'form-control select2-customer', 'placeholder' => 'Pick a Customer...']) !!}
        <small class="form-text text-muted">{{ __('focs.edit_single_customer_hint') }}</small>
    </div>
@else
    {{-- CREATE MODE: Multiple Customer Selection with Select All --}}
    <div class="form-group col-sm-6">
        {!! Form::label('customer_ids', __('invoices.customer')) !!}<span class="asterisk"> *</span>
        
        <!-- Select All Checkbox -->
        <div class="mb-2">
            <label class="checkbox-inline">
                <input type="checkbox" id="selectAllCustomers"> <strong>{{ __('Select All Customers') }}</strong>
            </label>
        </div>
        
        <select name="customer_ids[]" id="customer_ids" class="form-control select2-customer-multiple" multiple="multiple" style="width: 100%;">
            @foreach($customerItems as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
@endif

<!-- Quantity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('quantity', __('focs.quantity')) !!}<span class="asterisk"> *</span>
    {!! Form::number('quantity', null, ['class' => 'form-control', 'min' => 0]) !!}
</div>

<!-- Free Product Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('free_product_id', __('focs.free_product')) !!}<span class="asterisk"> *</span>
    <select name="free_product_id" id="free_product_id" class="form-control select2-free-product" style="width: 100%;">
        <option value="">Pick a Free Product...</option>
        @foreach($productData as $product)
            <option value="{{ $product['id'] }}" data-code="{{ $product['code'] }}" {{ (isset($foc) && $foc->free_product_id == $product['id']) ? 'selected' : '' }}>
                {{ $product['name'] }} ({{ $product['code'] }})
            </option>
        @endforeach
    </select>
</div>

<!-- Free Quantity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('free_quantity', __('focs.free_quantity')) !!}<span class="asterisk"> *</span>
    {!! Form::text('free_quantity', null, ['class' => 'form-control']) !!}
</div>

<!-- Startdate Field -->
<div class="form-group col-sm-6">
    {!! Form::label('startdate', __('focs.start_date')) !!}<span class="asterisk"> *</span>
    {!! Form::text('startdate', isset($foc) ? \Carbon\Carbon::parse($foc->startdate)->format('d-m-Y') : null, ['class' => 'form-control', 'id' => 'startdate']) !!}
</div>

<!-- Enddate Field -->
<div class="form-group col-sm-6">
    {!! Form::label('enddate', __('focs.end_date')) !!}<span class="asterisk"> *</span>
    {!! Form::text('enddate', isset($foc) ? \Carbon\Carbon::parse($foc->enddate)->format('d-m-Y') : null, ['class' => 'form-control', 'id' => 'enddate']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', __('focs.status')) !!}
    {{ Form::select('status', [
        1 => __('focs.active'),
        0 => __('focs.unactive'),
    ], isset($foc) ? $foc->status : 1, ['class' => 'form-control']) }}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit(isset($foc) ? __('Update') : __('focs.save'), ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('focs.index') }}" class="btn btn-secondary">{{ __('focs.cancel') }}</a>
</div>

@push('scripts')
    <script>
        $('#startdate').datetimepicker({
            format: 'DD-MM-YYYY',
            useCurrent: true,
            icons: {
                up: "icon-arrow-up-circle icons font-2xl",
                down: "icon-arrow-down-circle icons font-2xl"
            },
            sideBySide: true
        });
        $('#enddate').datetimepicker({
            format: 'DD-MM-YYYY',
            useCurrent: true,
            icons: {
                up: "icon-arrow-up-circle icons font-2xl",
                down: "icon-arrow-down-circle icons font-2xl"
            },
            sideBySide: true
        });

        $(document).ready(function () {
            // Custom matcher for Select2 to search by both name and code
            function matchCustom(params, data) {
                // If there are no search terms, return all of the data
                if ($.trim(params.term) === '') {
                    return data;
                }

                // Skip if there is no 'element' property (for grouped data)
                if (typeof data.element === 'undefined') {
                    return null;
                }

                // Get the product code from the data-code attribute and ensure it's a string
                var productCode = $(data.element).data('code');
                var searchTerm = params.term.toLowerCase();
                
                // Convert productCode to string if it exists, otherwise empty string
                var productCodeStr = productCode ? String(productCode).toLowerCase() : '';
                var textStr = data.text ? String(data.text).toLowerCase() : '';
                
                // Check if search term matches product name OR product code
                if (textStr.indexOf(searchTerm) > -1 || 
                    (productCodeStr && productCodeStr.indexOf(searchTerm) > -1)) {
                    // Modified to return the modified data object
                    var modifiedData = $.extend({}, data, true);
                    return modifiedData;
                }

                // Return `null` if the term should not be displayed
                return null;
            }

            // Initialize Select2 for product field with custom matcher
            $('.select2-product').select2({
                placeholder: "Search by product name or code...",
                allowClear: true,
                width: '100%',
                matcher: matchCustom,
                language: {
                    searching: function() {
                        return "Searching...";
                    },
                    noResults: function() {
                        return "No product found. Try searching by name or code.";
                    }
                }
            });
            
            // Initialize Select2 for free product field with custom matcher
            $('.select2-free-product').select2({
                placeholder: "Search by product name or code...",
                allowClear: true,
                width: '100%',
                matcher: matchCustom,
                language: {
                    searching: function() {
                        return "Searching...";
                    },
                    noResults: function() {
                        return "No product found. Try searching by name or code.";
                    }
                }
            });
            
            // Check if we are in CREATE mode (no 'foc' variable or no id)
            var isEditMode = {{ isset($foc) && $foc->id ? 'true' : 'false' }};
            
            @if(!(isset($foc) && $foc->id))
                // CREATE MODE: Initialize multiple customer selection
                $('.select2-customer-multiple').select2({
                    placeholder: "Search for customers...",
                    allowClear: true,
                    width: '100%',
                    language: {
                        searching: function() {
                            return "Searching...";
                        },
                        noResults: function() {
                            return "No customers found.";
                        }
                    }
                });
                
                // Select All Customers functionality
                $('#selectAllCustomers').on('change', function() {
                    if ($(this).is(':checked')) {
                        // Select all options
                        $('.select2-customer-multiple > option').prop('selected', true);
                        $('.select2-customer-multiple').trigger('change');
                    } else {
                        // Deselect all options
                        $('.select2-customer-multiple > option').prop('selected', false);
                        $('.select2-customer-multiple').trigger('change');
                    }
                });
                
                // Update select all checkbox state when individual selections change
                $('.select2-customer-multiple').on('change', function() {
                    var totalOptions = $(this).find('option').length;
                    var selectedOptions = $(this).find('option:selected').length;
                    
                    if (selectedOptions === totalOptions) {
                        $('#selectAllCustomers').prop('checked', true);
                    } else {
                        $('#selectAllCustomers').prop('checked', false);
                    }
                });
            @else
                // EDIT MODE: Initialize single customer selection
                $('.select2-customer').select2({
                    placeholder: "Search for a customer...",
                    allowClear: true,
                    width: '100%'
                });
            @endif
            
            HideLoad();
        });
    </script>
    
    <style>
        /* Optional: Style the Select2 dropdowns to match your theme */
        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da;
            border-radius: .25rem;
            height: 38px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        
        /* Style for multiple select */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: .25rem;
            min-height: 38px;
        }
        
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        
        /* Checkbox styling */
        .checkbox-inline {
            cursor: pointer;
            user-select: none;
        }
        
        .checkbox-inline input {
            margin-right: 5px;
            cursor: pointer;
        }
    </style>
@endpush