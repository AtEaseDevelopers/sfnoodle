<!-- Invoiceno Field -->
<div class="form-group">
    {!! Form::label('invoiceno', 'Order No') !!}:<span class="asterisk"> *</span>
    <p>{{ $salesInvoice->invoiceno }}</p>
</div>

<!-- Date Field -->
<div class="form-group">
    {!! Form::label('date', 'Date') !!}:<span class="asterisk"> *</span>
    <p>{{ $salesInvoice->date ? date('d-m-Y', strtotime($salesInvoice->date)) : '' }}</p>
</div>

<!-- Customer Id Field -->
<div class="form-group">
    {!! Form::label('customer_id', 'Customer') !!}:<span class="asterisk"> *</span>
    <p>{{ $salesInvoice->customer->company ?? '' }}</p>
</div>

<!-- Paymentterm Field -->
<div class="form-group">
    {!! Form::label('paymentterm', 'Payment Term') !!}:<span class="asterisk"> *</span>
    <p>
        {{ $salesInvoice->paymentterm ?? 'Unknown' }}
       
    </p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status') !!}:<span class="asterisk"> *</span>
    @php
        $statuses = [
            0 => 'Pending',
            1 => 'Cancelled'
        ];
    @endphp
    <p>{{ $statuses[$salesInvoice->status] ?? 'Unknown' }}</p>
</div>

<!-- Creator Information -->
<div class="form-group">
    {!! Form::label('created_by', 'Created By') !!}:
    <p>{{ $salesInvoice->creator->name ?? 'System' }} ({{ $salesInvoice->is_driver ? 'Agent' : 'User' }})</p>
</div>


<!-- Remark Field -->
<div class="form-group">
    {!! Form::label('remark', 'Remark') !!}:
    <p>{{ $salesInvoice->remark ?? '-' }}</p>
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