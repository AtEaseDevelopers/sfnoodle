<!-- Invoice Id Field -->
<div class="form-group">
    {!! Form::label('invoice_id', __('invoice_payments.invoice')) !!}:
    <p>{{ $invoicePayment->invoice->invoiceno ?? '' }}</p>
</div>

<!-- Type Field -->
<div class="form-group">
    {!! Form::label('type', __('invoice_payments.type')) !!}:
    @if($invoicePayment->type == 1)
         <p>{{ __('invoice_payments.cash') }}</p>
    @elseif($invoicePayment->type == 2)
        <p>{{ __('invoice_payments.credit') }}</p>
    @elseif($invoicePayment->type == 3)
        <p>{{ __('invoice_payments.online_bankin') }}</p>
    @elseif($invoicePayment->type == 4)
        <p>{{ __('invoice_payments.ewallet') }}</p>
    @elseif($invoicePayment->type == 5)
        <p>{{ __('invoice_payments.cheque') }} {{ '-' . $invoicePayment->chequeno }}</p>
    @else
        <p>{{ __('invoice_payments.payment_term_unknown') }}</p>
    @endif
</div>

<!-- Customer Id Field -->
<div class="form-group">
    {!! Form::label('customer_id', __('invoice_payments.customer')) !!}:
    <p>{{ $invoicePayment->customer->company ?? '' }}</p>
</div>

<!-- Amount Field -->
<div class="form-group">
    {!! Form::label('amount', __('invoice_payments.amount')) !!}:
    <p>{{ number_format($invoicePayment->amount, 2) }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', __('invoice_payments.status')) !!}:
    @if($invoicePayment->status == 0)
        <p>{{ __('invoice_payments.new') }}</p>
    @elseif($invoicePayment->status == 1)
        <p>{{ __('invoice_payments.completed') }}</p>
    @elseif($invoicePayment->status == 2)
        <p>{{ __('invoice_payments.cancelled') }}</p>
    @else
        <p>{{ $invoicePayment->status }}</p>
    @endif
</div>

<!-- Attachment Field -->
<div class="form-group">
    {!! Form::label('attachment', __('invoice_payments.attachment')) !!}:
    @if($invoicePayment->attachment)
        <p>
            <a href="{{ asset('/' . $invoicePayment->attachment) }}" target="_blank" class="btn btn-link">
                <i class="fa fa-paperclip"></i> {{ __('View Attachment') }}
            </a>
        </p>
    @else
        <p>-</p>
    @endif
</div>

<!-- Approve By Field -->
<div class="form-group">
    {!! Form::label('approve_by', __('invoice_payments.approve_by')) !!}:
    <p>{{ $invoicePayment->approve_by ?: '-' }}</p>
</div>

<!-- Approve At Field -->
<div class="form-group">
    {!! Form::label('approve_at', __('invoice_payments.approve_at')) !!}:
    <p>{{ $invoicePayment->approve_at ?: '-' }}</p>
</div>

<!-- Remark Field -->
<div class="form-group">
    {!! Form::label('remark', __('invoice_payments.remark')) !!}:
    <p>{{ $invoicePayment->remark ?: '-' }}</p>
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