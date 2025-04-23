<!-- Product Id Field -->
<div class="form-group">
    {!! Form::label('product_id', 'Product:') !!}
    <p>{{ $specialPrice->product->name }}</p>
</div>

<!-- Customer Id Field -->
<div class="form-group">
    {!! Form::label('customer_id', 'Customer:') !!}
    <p>{{ $specialPrice->customer->company }}</p>
</div>

<!-- Price Field -->
<div class="form-group">
    {!! Form::label('price', 'Price:') !!}
    <p>{{ number_format($specialPrice->price,2) }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $specialPrice->status == 1 ? "Active" : "Unactive" }}</p>
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
