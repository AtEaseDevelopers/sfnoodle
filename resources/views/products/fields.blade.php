<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', __('products.code')) !!}<span class="asterisk"> *</span>
    {!! Form::text('code', null, ['class' => 'form-control', 'maxlength' => 255, 'autofocus']) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', __('products.name')) !!}<span class="asterisk"> *</span>
    {!! Form::text('name', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Price Field -->
<div class="form-group col-sm-6">
    {!! Form::label('price', __('products.price')) !!}<span class="asterisk"> *</span>
    {!! Form::number('price', null, ['class' => 'form-control', 'step' => '0.01']) !!}
</div>

<!-- Category Field with Searchable Dropdown -->
<div class="form-group col-sm-6">
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
<div class="form-group col-sm-6">
    {!! Form::label('uom', __('UOM')) !!}
    {!! Form::text('uom', null, ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'e.g., KG, PCS, LITER']) !!}
    <small class="text-muted">Optional: Enter unit of measurement</small>
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', __('products.status')) !!}
    {{ Form::select('status', [
        1 => __('products.active'),
        0 => __('products.unactive'),
    ], old('status', isset($product) ? $product->status : null), ['class' => 'form-control']) }}
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
                tags: true, // Allows creating new options
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
                // Check if the category exists in the dropdown options
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
                    // If it doesn't exist, create a new option
                    var newOption = new Option(oldCategory, oldCategory, true, true);
                    $('#category_select').append(newOption).trigger('change');
                }
            }
            
            $(document).keyup(function(e) {
                if (e.key === "Escape") {
                    $('form a.btn-secondary')[0].click();
                }
            });
        });
    </script>
@endpush

@push('css')
    <style>
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
    </style>
@endpush