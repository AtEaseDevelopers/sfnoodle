<!-- Product Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('product_id', __('focs.product')) !!}<span class="asterisk"> *</span>
    <select name="product_id" id="product_id" class="form-control select2-product" style="width: 100%;">
        <option value="">Pick a Product...</option>
        @foreach($productData as $product)
            <option value="{{ $product['id'] }}" data-code="{{ $product['code'] }}">
                {{ $product['name'] }} ({{ $product['code'] }})
            </option>
        @endforeach
    </select>
</div>

<!-- Customer Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('customer_id', __('invoices.customer')) !!}<span class="asterisk"> *</span>
    {!! Form::select('customer_id', $customerItems, null, ['class' => 'form-control select2-customer', 'placeholder' => 'Pick a Customer...']) !!}
</div>

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
            <option value="{{ $product['id'] }}" data-code="{{ $product['code'] }}">
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
    {!! Form::text('startdate', null, ['class' => 'form-control', 'id' => 'startdate']) !!}
</div>

<!-- Enddate Field -->
<div class="form-group col-sm-6">
    {!! Form::label('enddate', __('focs.end_date')) !!}<span class="asterisk"> *</span>
    {!! Form::text('enddate', null, ['class' => 'form-control', 'id' => 'enddate']) !!}
</div>


<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', __('focs.status')) !!}
    {{ Form::select('status', [
        1 => __('focs.active'),
        0 => __('focs.unactive'),
    ], null, ['class' => 'form-control']) }}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit(__('focs.save'), ['class' => 'btn btn-primary']) !!}
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
            
            // Initialize Select2 for customer field
            $('.select2-customer').select2({
                placeholder: "Search for a customer...",
                allowClear: true,
                width: '100%'
            });
            
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
    </style>
@endpush