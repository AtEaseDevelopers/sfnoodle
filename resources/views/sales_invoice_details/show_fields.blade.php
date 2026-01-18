<!-- Sales Invoice Id Field -->
<div class="form-group">
    {!! Form::label('sales_invoice_id', 'Sales Invoice') !!}
    <p>{{ $salesInvoiceDetail->salesInvoice->invoiceno }}</p>
</div>

<!-- Product Id Field -->
<div class="form-group">
    {!! Form::label('product_id', 'Product') !!}
    <p>{{ $salesInvoiceDetail->product->name }}</p>
</div>

<!-- Quantity Field -->
<div class="form-group">
    {!! Form::label('quantity', 'Quantity') !!}
    <p>{{ $salesInvoiceDetail->quantity }}</p>
</div>

<!-- Price Field -->
<div class="form-group">
    {!! Form::label('price', 'Price') !!}
    <p>{{ number_format($salesInvoiceDetail->price, 2) }}</p>
</div>

<!-- TotalPrice Field -->
<div class="form-group">
    {!! Form::label('totalprice', 'Total Price') !!}
    <p>{{ number_format($salesInvoiceDetail->totalprice, 2) }}</p>
</div>

<!-- Remark Field -->
<div class="form-group">
    {!! Form::label('remark', 'Remark') !!}
    <p>{{ $salesInvoiceDetail->remark }}</p>
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