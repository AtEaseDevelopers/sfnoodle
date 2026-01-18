<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', 'Code') !!}:<span class="asterisk"> *</span>
    {!! Form::text('code', null, ['class' => 'form-control', 'maxlength' => 255, 'autofocus']) !!}
</div>

<!-- Company Field -->
<div class="form-group col-sm-6">
    {!! Form::label('company', 'Company') !!}:<span class="asterisk"> *</span>
    {!! Form::text('company', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Paymentterm Field -->
<div class="form-group col-sm-6">
    {!! Form::label('paymentterm', 'Payment Term') !!}:<span class="asterisk"> *</span>
    {{ Form::select('paymentterm', [
        'Cash' => 'Cash',
        'Credit' => 'Credit',
    ], null, ['class' => 'form-control']) }}
</div>

<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone', 'Phone') !!}:
    {!! Form::text('phone', null, ['class' => 'form-control', 'maxlength' => 20]) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address', 'Address') !!}:
    {!! Form::text('address', null, ['class' => 'form-control', 'maxlength' => 65535]) !!}
</div>

<!-- Sst Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sst', 'SST') !!}:
    {!! Form::text('sst', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Tin Field -->
<div class="form-group col-sm-6">
    {!! Form::label('tin', 'TIN') !!}:
    {!! Form::text('tin', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status') !!}:<span class="asterisk"> *</span>
    {{ Form::select('status', [
        1 => 'Active',
        0 => 'Inactive',
    ], null, ['class' => 'form-control']) }}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
</div>

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('form a.btn-secondary')[0].click();
            }
        });
        $(document).ready(function () {
            HideLoad();
        });
    </script>
@endpush