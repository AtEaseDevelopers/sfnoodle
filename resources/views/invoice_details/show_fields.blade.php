<!-- Invoice Id Field -->
<div class="form-group">
    {!! Form::label('invoice_id', 'Invoice:') !!}
    <p>{{ $invoiceDetail->invoice->invoiceno }}</p>
</div>

<!-- Product Id Field -->
<div class="form-group">
    {!! Form::label('product_id', 'Product:') !!}
    <p>{{ $invoiceDetail->product->name }}</p>
</div>

<!-- Quantity Field -->
<div class="form-group">
    {!! Form::label('quantity', 'Quantity:') !!}
    <p>{{ $invoiceDetail->quantity }}</p>
</div>

<!-- Price Field -->
<div class="form-group">
    {!! Form::label('price', 'Price:') !!}
    <p>{{ number_format($invoiceDetail->price,2) }}</p>
</div>

<!-- TotalPrice Field -->
<div class="form-group">
    {!! Form::label('totalprice', 'Total Price:') !!}
    <p>{{ number_format($invoiceDetail->totalprice,2) }}</p>
</div>

<!-- Remark Field -->
<div class="form-group">
    {!! Form::label('remark', 'Remark:') !!}
    <p>{{ $invoiceDetail->remark }}</p>
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