<!-- Code Field -->
<div class="form-group">
    {!! Form::label('code', 'Code:') !!}
    <p>{{ $customer->code }}</p>
</div>

<!-- company Field -->
<div class="form-group">
    {!! Form::label('company', 'Company:') !!}
    <p>{{ $customer->company }}</p>
</div>
@php
    if($customer->paymentterm == 1){
        $paymentterm = "Cash";
    }
    if($customer->paymentterm == 2){
        $paymentterm = "Bankin";
    }
    if($customer->paymentterm == 3){
        $paymentterm = "Credit Note";
    }
@endphp
<!-- Paymentterm Field -->
<div class="form-group">
    {!! Form::label('paymentterm', 'Payment Term:') !!}
    <p>{{ $paymentterm }}</p>
</div>

<!-- Group Field -->
<div class="form-group">
    {!! Form::label('group', 'Group:') !!}
    <p>{{ $customer->group ?? ''}}</p>
</div>

<!-- Agent Id Field -->
<div class="form-group">
    {!! Form::label('agent_id', 'Agent:') !!}
    <p>{{ $customer->agent->name ?? '' }}</p>
</div>

<!-- Supervisor Id Field -->
<div class="form-group">
    {!! Form::label('supervisor_id', 'Operation:') !!}
    <p>{{ $customer->supervisor->name ?? '' }}</p>
</div>

<!-- Phone Field -->
<div class="form-group">
    {!! Form::label('phone', 'Phone:') !!}
    <p>{{ $customer->phone }}</p>
</div>

<!-- Address Field -->
<div class="form-group">
    {!! Form::label('address', 'Address:') !!}
    <p>{{ $customer->address }}</p>
</div>

<!-- Sst Field -->
<div class="form-group">
    {!! Form::label('sst', 'Sst:') !!}
    <p>{{ $customer->sst }}</p>
</div>

<!-- Tin Field -->
<div class="form-group">
    {!! Form::label('tin', 'Tin:') !!}
    <p>{{ $customer->tin }}</p>
</div>
<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $customer->status == 1 ? "Active" : "Unactive" }}</p>
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
