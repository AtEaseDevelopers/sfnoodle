<!-- Invoice Id Field -->
<div class="form-group">
    {!! Form::label('invoice_id', 'Invoice:') !!}
    <p>{{ $invoicePayment->invoice->invoiceno ?? '' }}</p>
</div>

<!-- Type Field -->
<div class="form-group">
    {!! Form::label('type', 'Type:') !!}
    @if($invoicePayment->type == 1)
         <p>Cash</p>
    @elseif(invoicePayment->type == 2)
        <p>Credit</p>
    @elseif(invoicePayment->type == 3)
        <p>Online BankIn</p>
    @elseif(invoicePayment->type == 4)
        <p>E-wallet</p>
    @elseif(invoicePayment->type == 5)
        <p>Cheque {{ '-' . $invoicePayment->chequeno}}</p>
    @else
        <p>Payment Term: Unknown</p>
    @endif
</div>

<!-- Customer Id Field -->
<div class="form-group">
    {!! Form::label('customer_id', 'Customer:') !!}
    <p>{{ $invoicePayment->customer->name ?? '' }}</p>
</div>

<!-- Amount Field -->
<div class="form-group">
    {!! Form::label('amount', 'Amount:') !!}
    <p>{{ number_format($invoicePayment->amount,2) }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $invoicePayment->status == 1 ? "Completed" : "New" }}</p>
</div>

<!-- Attachment Field -->
<div class="form-group">
    {!! Form::label('attachment', 'Attachment:') !!}
    <p>{{ $invoicePayment->attachment }}</p>
</div>

<!-- Approve By Field -->
<div class="form-group">
    {!! Form::label('approve_by', 'Approve By:') !!}
    <p>{{ $invoicePayment->approve_by }}</p>
</div>

<!-- Approve At Field -->
<div class="form-group">
    {!! Form::label('approve_at', 'Approve At:') !!}
    <p>{{ $invoicePayment->approve_at }}</p>
</div>

<!-- Remark Field -->
<div class="form-group">
    {!! Form::label('remark', 'Remark:') !!}
    <p>{{ $invoicePayment->remark }}</p>
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