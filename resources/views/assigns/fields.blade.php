<!-- Driver Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('driver_id', 'Driver:') !!}<span class="asterisk"> *</span>
    {!! Form::select('driver_id', $driverItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Driver...','autofocus']) !!}
</div>


<!-- Customer Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('customer_id', 'Customer:') !!}<span class="asterisk"> *</span>
    {!! Form::select('customer_id', $customerItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Customer...']) !!}
</div>


<!-- Sequence Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sequence', 'Sequence:') !!}<span class="asterisk"> *</span>
    {!! Form::number('sequence', null, ['class' => 'form-control', 'min' => 0]) !!}
</div>


<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('assigns.index') }}" class="btn btn-secondary">Cancel</a>
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