<!-- Product Id Field -->
<div class="form-group">
    {!! Form::label('product_id', 'Product Id:') !!}
    <p>{{ $foc->product->name }}</p>
</div>

<!-- Customer Id Field -->
<div class="form-group">
    {!! Form::label('customer_id', 'Customer Id:') !!}
    <p>{{ $foc->customer->company }}</p>
</div>

<!-- Quantity Field -->
<div class="form-group">
    {!! Form::label('quantity', 'Quantity:') !!}
    <p>{{ $foc->quantity }}</p>
</div>

<!-- Free Product Id Field -->
<div class="form-group">
    {!! Form::label('free_product_id', 'Free Product Id:') !!}
    <p>{{ $foc->product->name }}</p>
</div>

<!-- Free Quantity Field -->
<div class="form-group">
    {!! Form::label('free_quantity', 'Free Quantity:') !!}
    <p>{{ $foc->free_quantity }}</p>
</div>

<!-- Startdate Field -->
<div class="form-group">
    {!! Form::label('startdate', 'Startdate:') !!}
    <p>{{ $foc->startdate }}</p>
</div>

<!-- Enddate Field -->
<div class="form-group">
    {!! Form::label('enddate', 'Enddate:') !!}
    <p>{{ $foc->enddate }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $foc->status == 1 ? "Active" : "Unactive" }}</p>
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