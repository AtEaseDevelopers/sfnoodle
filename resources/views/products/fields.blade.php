<div class="row">
    <!-- Left Column -->
    <div class="col-md-6">
        <!-- Code Field -->
        <div class="form-group col-sm-12">
            {!! Form::label('code', __('products.code')) !!}<span class="asterisk"> *</span>
            {!! Form::text('code', null, ['class' => 'form-control', 'maxlength' => 255, 'autofocus']) !!}
        </div>

        <!-- Name Field -->
        <div class="form-group col-sm-12">
            {!! Form::label('name', __('products.name')) !!}<span class="asterisk"> *</span>
            {!! Form::text('name', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
        </div>

        <!-- Price Field -->
        <div class="form-group col-sm-12">
            {!! Form::label('price', __('products.price')) !!}<span class="asterisk"> *</span>
            {!! Form::number('price', null, ['class' => 'form-control', 'step' => '0.01']) !!}
        </div>

        <!-- Category Field with Searchable Dropdown -->
        <div class="form-group col-sm-12">
            {!! Form::label('category', __('Category')) !!}<span class="asterisk"> *</span>
            <select name="category" id="category_select" class="form-control" required>
                <option value="">Select or type new category...</option>
                @foreach($existingCategories as $cat)
                    <option value="{{ $cat }}" 
                        {{ (old('category') == $cat) ? 'selected' : 
                           ((isset($product) && $product->category == $cat && !old('category')) ? 'selected' : '') }}>
                        {{ $cat }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Type to search existing categories or type new category name</small>
        </div>

        <!-- UOM Field --> 
        <div class="form-group col-sm-12">
            {!! Form::label('uom', __('UOM')) !!}
            {!! Form::text('uom', null, ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'e.g., KG, PCS, LITER']) !!}
            <small class="text-muted">Optional: Enter unit of measurement</small>
        </div>

        <!-- Status Field -->
        <div class="form-group col-sm-12">
            {!! Form::label('status', __('products.status')) !!}
            {{ Form::select('status', [
                1 => __('products.active'),
                0 => __('products.unactive'),
            ], old('status', isset($product) ? $product->status : null), ['class' => 'form-control']) }}
        </div>
    </div>

    <!-- Right Column - Image Upload -->
    <div class="col-md-6">
        <div class="form-group col-sm-12">
            {!! Form::label('image', __('Product Image')) !!}
            
            <!-- Current Image Display (for edit mode) -->
            @if(isset($product) && $product->image_path)
                <div class="mb-3">
                    <p class="mb-2"><strong>Current Image:</strong></p>
                    @php
                        $imagePath = $product->image_path;
                        $fullImagePath = public_path($imagePath);
                        $isImage = in_array(strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
                    @endphp
                    
                    @if($isImage && file_exists($fullImagePath))
                        <div class="text-center mb-2" id="current-image-container">
                            <img src="{{ asset($imagePath) }}" 
                                 alt="{{ $product->name }}" 
                                 class="img-thumbnail" 
                                 style="max-width: 200px; max-height: 200px; object-fit: cover;">
                            <div class="mt-2">
                                <button type="button" class="btn btn-danger btn-sm" id="remove_image_btn">
                                    <i class="fas fa-trash"></i> Remove Image
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Image file not found on server.
                        </div>
                    @endif
                </div>
            @elseif(isset($product) && !$product->image_path)
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> No image uploaded for this product.
                </div>
            @endif
            
            <!-- File Input -->
            <div class="custom-file mt-2">
                <input type="file" class="custom-file-input" name="image" id="product_image" 
                       accept=".jpg, .jpeg, .png, .gif">
                <label id="product_image_label" class="custom-file-label" for="product_image">
                    @if(isset($product) && $product->image_path)
                        Replace file (Current: {{ basename($product->image_path) }})
                    @else
                        Choose file
                    @endif
                </label>
            </div>
            <small class="form-text text-muted">
                Accept .jpg, .jpeg, .png, .gif (Max: 2MB)
            </small>
        </div>
        
        <!-- New Image Preview -->
        <div class="form-group col-sm-12">
            <div id="new-image-preview-container" style="display: none;">
                <p class="mb-1"><strong>New Image Preview:</strong></p>
                <div class="text-center">
                    <img id="new-image-preview" src="" alt="Preview" 
                         class="img-thumbnail" 
                         style="max-width: 200px; max-height: 200px; object-fit: cover;">
                </div>
            </div>
        </div>
        <!-- Tiered Pricing Section -->
        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Volume Pricing</h5>
                </div>
                <div class="card-body">
                    
                    <table class="table table-bordered" id="tiered-pricing-table">
                        <thead>
                            <tr>
                                <th style="width: 40%">Quantity</th>
                                <th style="width: 40%">Price per Unit ({{ config('app.currency', 'RM') }})</th>
                                <th style="width: 20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $tieredPricing = isset($product) && $product->tiered_pricing ? $product->tiered_pricing : old('tiered_pricing', []);
                                if (empty($tieredPricing) && old('tiered_pricing') === null) {
                                    // Add one empty row as default
                                    $tieredPricing = [['quantity' => '', 'price' => '']];
                                }
                            @endphp
                            
                            @foreach($tieredPricing as $index => $tier)
                                <tr class="tier-row">
                                    <td>
                                        <input type="number" 
                                            name="tiered_pricing[{{ $index }}][quantity]" 
                                            class="form-control tier-quantity" 
                                            placeholder="" 
                                            value="{{ old("tiered_pricing.{$index}.quantity", $tier['quantity'] ?? '') }}"
                                            min="1" step="1">
                                    </td>
                                    <td>
                                        <input type="number" 
                                            name="tiered_pricing[{{ $index }}][price]" 
                                            class="form-control tier-price" 
                                            placeholder="" 
                                            value="{{ old("tiered_pricing.{$index}.price", $tier['price'] ?? '') }}"
                                            min="0" step="0.01">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-tier-row" {{ $loop->first && count($tieredPricing) == 1 ? 'disabled' : '' }}>
                                            </i> Remove
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">
                                    <button type="button" class="btn btn-success btn-sm" id="add-tier-row">
                                        </i> Add 
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <!-- Preview calculated pricing -->
                    <div class="mt-3" id="pricing-preview" style="display: none;">
                        <div class="alert alert-success">
                            <strong><i class="fas fa-calculator"></i> Pricing Preview:</strong>
                            <div id="preview-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit(__('products.save'), ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('products.index') }}" class="btn btn-secondary">{{ __('products.cancel') }}</a>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <script>
        

        $(document).ready(function () {
            HideLoad();
            
            // Get the old value or current product category
            var oldCategory = '{{ old('category', isset($product) ? $product->category : '') }}';
            
            // Initialize Select2 with tagging
            $('#category_select').select2({
                placeholder: "Select or type new category",
                allowClear: true,
                tags: true,
                createTag: function(params) {
                    var term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newOption: true
                    }
                },
                language: {
                    noResults: function() {
                        return "Type to create new category";
                    }
                }
            });
            
            // Set the value if there's an old category or existing product category
            if (oldCategory && oldCategory !== '') {
                var exists = false;
                $('#category_select option').each(function() {
                    if ($(this).val() === oldCategory) {
                        exists = true;
                        return false;
                    }
                });
                
                if (exists) {
                    $('#category_select').val(oldCategory).trigger('change');
                } else {
                    var newOption = new Option(oldCategory, oldCategory, true, true);
                    $('#category_select').append(newOption).trigger('change');
                }
            }
            
            // File input label update
            $("#product_image").on("change", function(){
                if(this.value != ''){
                    var fileName = $(this).val().split('\\').pop();
                    $('#product_image_label').html(fileName);
                    // Show preview container and hide current image
                    $('#current-image-container').parent().parent().hide();
                } else {
                    @if(isset($product) && $product->image_path)
                        $('#product_image_label').html('Replace file (Current: {{ basename($product->image_path) }})');
                        $('#current-image-container').parent().parent().show();
                    @else
                        $('#product_image_label').html('Choose file');
                    @endif
                }
            });
            
            // Preview image before upload
            $("#product_image").on("change", function(e) {
                var file = e.target.files[0];
                
                $('#new-image-preview-container').hide();
                
                if (file) {
                    if (file.type.match('image.*')) {
                        var reader = new FileReader();
                        
                        reader.onload = function(e) {
                            $('#new-image-preview').attr('src', e.target.result);
                            $('#new-image-preview-container').show();
                            // Hide current image container when new image is selected
                            if ($('#current-image-container').length) {
                                $('#current-image-container').hide();
                            }
                        }
                        
                        reader.readAsDataURL(file);
                    } else {
                        $('#new-image-preview').attr('src', '');
                        $('#new-image-preview-container').hide();
                        alert('Please select a valid image file (jpg, jpeg, png, gif)');
                        $(this).val('');
                        @if(isset($product) && $product->image_path)
                            $('#product_image_label').html('Replace file (Current: {{ basename($product->image_path) }})');
                        @else
                            $('#product_image_label').html('Choose file');
                        @endif
                        if ($('#current-image-container').length) {
                            $('#current-image-container').show();
                        }
                    }
                }
            });
            
            // Remove image functionality (only for edit mode)
            $('#remove_image_btn').on('click', function() {
                if (confirm('Are you sure you want to remove this image?')) {
                    var productId = '{{ isset($product) ? Crypt::encrypt($product->id) : '' }}';
                    if (productId) {
                        $.ajax({
                            url: '{{ route("products.remove-image", ":id") }}'.replace(':id', productId),
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#current-image-container').fadeOut('slow', function() {
                                        $(this).remove();
                                        // Show no image message
                                        $('#current-image-container').parent().parent().append(
                                            '<div class="alert alert-info mt-2"><i class="fas fa-info-circle"></i> Image has been removed. You can upload a new one.</div>'
                                        );
                                    });
                                    alert('Image removed successfully');
                                } else {
                                    alert('Failed to remove image');
                                }
                            },
                            error: function() {
                                alert('Failed to remove image');
                            }
                        });
                    }
                }
            });
            
            $(document).keyup(function(e) {
                if (e.key === "Escape") {
                    $('form a.btn-secondary')[0].click();
                }
            });


            function updatePricingPreview() {
                var tiers = [];
                var basePrice = parseFloat($('#price').val()) || 0;
                
                $('.tier-row').each(function() {
                    var quantity = $(this).find('.tier-quantity').val();
                    var price = $(this).find('.tier-price').val();
                    
                    if (quantity && price && quantity > 0 && price >= 0) {
                        tiers.push({
                            quantity: parseInt(quantity),
                            price: parseFloat(price)
                        });
                    }
                });
                
                if (tiers.length === 0) {
                    $('#pricing-preview').hide();
                    return;
                }
                
                // Sort by quantity ascending
                tiers.sort(function(a, b) {
                    return a.quantity - b.quantity;
                });
                
                var previewHtml = '<table class="table table-sm table-bordered mt-2">';
                previewHtml += '<thead><tr><th>Quantity Range</th><th>Unit Price</th><th>Total Price</th></tr></thead>';
                previewHtml += '<tbody>';
                
                // Add regular price range for quantities below first tier (this should be FIRST)
                if (tiers.length > 0 && tiers[0].quantity > 1) {
                    previewHtml += '<tr class="table-info">';
                    previewHtml += '<td>1 - ' + (tiers[0].quantity - 1) + ' units</td>';
                    previewHtml += '<td>' + formatCurrency(basePrice) + '</td>';
                    previewHtml += '<td>' + formatCurrency(basePrice * (tiers[0].quantity - 1)) + ' (max for this range)</td>';
                    previewHtml += '</tr>';
                }
                
                // Then add all tiered pricing ranges
                for (var i = 0; i < tiers.length; i++) {
                    var tier = tiers[i];
                    var rangeStart = tier.quantity;
                    var rangeEnd = (i < tiers.length - 1) ? (tiers[i + 1].quantity - 1) : '+';
                    var rangeText = rangeStart + (rangeEnd === '+' ? '+' : ' - ' + rangeEnd) + ' units';
                    
                    previewHtml += '<tr>';
                    previewHtml += '<td>' + rangeText + '</td>';
                    previewHtml += '<td>' + formatCurrency(tier.price) + '</td>';
                    previewHtml += '<td>' + formatCurrency(tier.price * tier.quantity) + ' (for ' + tier.quantity + ' units)</td>';
                    previewHtml += '</tr>';
                }
                
                previewHtml += '</tbody></table>';
                
                $('#preview-content').html(previewHtml);
                $('#pricing-preview').show();
            }
            
            // Format currency
            function formatCurrency(amount) {
                return '{{ config('app.currency', 'RM') }} ' + amount.toFixed(2);
            }
            
            // Add new tier row
            $('#add-tier-row').click(function() {
                var rowCount = $('.tier-row').length;
                var newRow = `
                    <tr class="tier-row">
                        <td>
                            <input type="number" 
                                name="tiered_pricing[${rowCount}][quantity]" 
                                class="form-control tier-quantity" 
                                placeholder="" 
                                min="1" step="1">
                        </td>
                        <td>
                            <input type="number" 
                                name="tiered_pricing[${rowCount}][price]" 
                                class="form-control tier-price" 
                                placeholder="" 
                                min="0" step="0.01">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm remove-tier-row">
                              Remove
                            </button>
                        </td>
                    </tr>
                `;
                $('#tiered-pricing-table tbody').append(newRow);
                updatePricingPreview();
                
                // Enable all remove buttons if there are multiple rows
                if ($('.tier-row').length > 1) {
                    $('.remove-tier-row').prop('disabled', false);
                }
            });
            
            // Remove tier row
            $(document).on('click', '.remove-tier-row', function() {
                if ($('.tier-row').length > 1) {
                    $(this).closest('tr').remove();
                    // Rename indexes
                    $('.tier-row').each(function(index) {
                        $(this).find('.tier-quantity').attr('name', `tiered_pricing[${index}][quantity]`);
                        $(this).find('.tier-price').attr('name', `tiered_pricing[${index}][price]`);
                    });
                    updatePricingPreview();
                    
                    // Disable remove button if only one row left
                    if ($('.tier-row').length === 1) {
                        $('.remove-tier-row').prop('disabled', true);
                    }
                }
            });
            
            // Update preview when inputs change
            $(document).on('change keyup', '.tier-quantity, .tier-price', function() {
                updatePricingPreview();
            });
            
            // Also update when base price changes
            $('#price').on('change keyup', function() {
                updatePricingPreview();
            });
            
            // Initialize preview if there are values
            updatePricingPreview();
            
            // Validation: Check for duplicate quantities before submit
            $('form').on('submit', function(e) {
                var quantities = [];
                var hasError = false;
                
                $('.tier-quantity').each(function() {
                    var qty = $(this).val();
                    if (qty && qty !== '') {
                        if (quantities.includes(qty)) {
                            alert('Duplicate quantity values are not allowed. Please ensure each tier has a unique minimum quantity.');
                            $(this).focus();
                            hasError = true;
                            return false;
                        }
                        quantities.push(qty);
                    }
                });
                
                if (hasError) {
                    e.preventDefault();
                    return false;
                }
            });

        });
    </script>
@endpush

@push('css')
    <style>
        #tiered-pricing-table .table {
            margin-bottom: 0;
        }
        #tiered-pricing-table input {
            font-size: 14px;
        }
        .remove-tier-row:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        #pricing-preview {
            margin-top: 15px;
        }
        #pricing-preview .table {
            font-size: 13px;
            margin-bottom: 0;
        }
        
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 5px;
            border: 1px solid #d2d6de;
            border-radius: 4px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .select2-container {
            width: 100% !important;
        }
        .asterisk {
            color: red;
            margin-left: 3px;
        }
        .custom-file-label::after {
            content: "Browse";
        }
        .img-thumbnail {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }
    </style>
@endpush