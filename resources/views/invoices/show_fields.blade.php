<!-- Invoiceno Field -->
<div class="form-group">
    {!! Form::label('invoiceno', 'Invoice No:') !!}
    <p>{{ $invoice->invoiceno }}</p>
</div>

<!-- Date Field -->
<div class="form-group">
    {!! Form::label('date', 'Date:') !!}
    <p>{{ $invoice->date }}</p>
</div>

<!-- Customer Id Field -->
<div class="form-group">
    {!! Form::label('customer_id', 'Customer:') !!}
    <p>{{ $invoice->customer->company ?? '' }}</p>
</div>

<!-- Driver Id Field -->
<div class="form-group">
    {!! Form::label('driver_id', 'Driver:') !!}
    <p>{{ $invoice->driver->name ?? '' }}</p>
</div>

<!-- Kelindan Id Field -->
<div class="form-group">
    {!! Form::label('kelindan_id', 'Kelindan:') !!}
    <p>{{ $invoice->kelindan->name ?? '' }}</p>
</div>

<!-- Agent Id Field -->
<div class="form-group">
    {!! Form::label('agent_id', 'Agent:') !!}
    <p>{{ $invoice->agent->name ?? '' }}</p>
</div>

<!-- Supervisor Id Field -->
<div class="form-group">
    {!! Form::label('supervisor_id', 'Supervisor:') !!}
    <p>{{ $invoice->supervisor->name ?? '' }}</p>
</div>

<!-- Paymentterm Field -->
<div class="form-group">
    {!! Form::label('paymentterm', 'Payment Term:') !!}
    @if($invoice->paymentterm == 1)
         <p>Cash</p>
    @elseif($invoice->paymentterm == 2)
        <p>Credit</p>
    @elseif($invoice->paymentterm == 3)
        <p>Online BankIn</p>
    @elseif($invoice->paymentterm == 4)
        <p>E-wallet</p>
    @elseif($invoice->paymentterm == 5)
        <p>Cheque {{ '-' . $invoice->chequeno}}</p>
    @else
        <p>Payment Term: Unknown</p>
    @endif
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $invoice->status == 1 ? "Completed" : "New" }}</p>
</div>

<!-- Remark Field -->
<div class="form-group">
    {!! Form::label('remark', 'Remark:') !!}
    <p>{{ $invoice->remark }}</p>
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
