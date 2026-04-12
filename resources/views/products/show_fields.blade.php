<div class="row">
    <!-- Left Column -->
    <div class="col-md-6">
        <!-- Code Field -->
        <div class="form-group">
            {!! Form::label('code', __('products.code')) !!}:
            <p>{{ $product->code }}</p>
        </div>

        <!-- Name Field -->
        <div class="form-group">
            {!! Form::label('name', __('products.name')) !!}:
            <p>{{ $product->name }}</p>
        </div>

        <!-- Price Field -->
        <div class="form-group">
            {!! Form::label('price', __('products.price')) !!}:
            <p>{{ number_format($product->price, 2) }}</p>
        </div>

        <!-- Category Field -->
        <div class="form-group">
            {!! Form::label('category', __('Category')) !!}:
            <p>{{ $product->category ?: 'N/A' }}</p>
        </div>

        <!-- UOM Field -->
        <div class="form-group">
            {!! Form::label('uom', __('UOM')) !!}:
            <p>{{ $product->uom ?? 'N/A' }}</p>
        </div>

        <!-- Status Field -->
        <div class="form-group">
            {!! Form::label('status', __('products.status')) !!}:
            <p>{{ $product->status == 1 ? __('products.active') : __('products.unactive') }}</p>
        </div>
    </div>

    <!-- Right Column - Product Image -->
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('image', __('Product Image')) !!}:
            <div class="text-center mt-2">
                @if($product->image_path && file_exists(public_path($product->image_path)))
                    <img src="{{ asset($product->image_path) }}" 
                         alt="{{ $product->name }}" 
                         style="max-width: 100%; max-height: 300px; object-fit: contain; border: 1px solid #ddd; padding: 10px;">
                @else
                    <div class="text-center p-5 border rounded bg-light">
                        <i class="fas fa-image fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No image available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

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
@endpush