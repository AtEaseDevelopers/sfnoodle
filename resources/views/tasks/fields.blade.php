<!-- Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('date', 'Date:') !!}<span class="asterisk"> *</span>
    {!! Form::text('date', null, ['class' => 'form-control','id'=>'date','autofocus']) !!}
</div>

@push('scripts')
   <script type="text/javascript">
           $('#date').datetimepicker({
               format: 'DD-MM-YYYY',
               useCurrent: true,
               icons: {
                   up: "icon-arrow-up-circle icons font-2xl",
                   down: "icon-arrow-down-circle icons font-2xl"
               },
               sideBySide: true
           })
       </script>
@endpush


<!-- Driver Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('driver_id', 'Driver:') !!}<span class="asterisk"> *</span>
    {!! Form::select('driver_id', $driverItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Driver...']) !!}
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

<!-- Invoice Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('invoice_id', 'Invoice:') !!}
    {!! Form::select('invoice_id', $invoiceItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Invoice...']) !!}
</div>


<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {{ Form::select('status', array(0 => 'New', 1 => 'In-Progress', 8 => 'Completed', 9 => 'Cancelled'), null, ['class' => 'form-control']) }}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Cancel</a>
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