<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', 'Code:') !!}<span class="asterisk"> *</span>
    {!! Form::text('code', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255,'autofocus']) !!}
</div>

<!-- Company Field -->
<div class="form-group col-sm-6">
    {!! Form::label('company', 'Company:') !!}<span class="asterisk"> *</span>
    {!! Form::text('company', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Chinese Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('chinese_name', 'Chinese Name:') !!}
    {!! Form::text('chinese_name', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Paymentterm Field -->
<div class="form-group col-sm-6">
    {!! Form::label('paymentterm', 'Payment Term:') !!}<span class="asterisk"> *</span>
    {{ Form::select('paymentterm', array(1 => 'Cash', 2 => 'Credit Note'), null, ['class' => 'form-control']) }}
</div>

<!-- Group Field -->
<div class="form-group col-sm-6">
    {!! Form::label('group', 'Group:') !!}
    {!! Form::select('group[]', $groups, explode(",",$customer->group ?? ""), ['class' => 'selectpicker form-control', 'multiple' => true]) !!}
    {{-- {!! Form::select('group[]', $groups, explode(",",$customer->group ?? ""), ['class' => 'selectpicker form-control', 'placeholder' => 'Select Group']) !!} --}}
</div>

<!-- Agent Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('agent_id', 'Agent:') !!}
    {!! Form::select('agent_id', $agentItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Agent...']) !!}
</div>


<!-- Supervisor Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('supervisor_id', 'Operation:') !!}
    {!! Form::select('supervisor_id', $supervisorItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Operation...']) !!}
</div>


<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone', 'Phone:') !!}
    {!! Form::text('phone', null, ['class' => 'form-control','maxlength' => 20,'maxlength' => 20]) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address', 'Address:') !!}
    {!! Form::text('address', null, ['class' => 'form-control','maxlength' => 65535,'maxlength' => 65535]) !!}
</div>


<!-- Sst Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sst', 'Ssm:') !!}
    {!! Form::text('sst', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Tin Field -->
<div class="form-group col-sm-6">
    {!! Form::label('tin', 'Tin:') !!}
    {!! Form::text('tin', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}<span class="asterisk"> *</span>
    {{ Form::select('status', array(1 => 'Active', 0 => 'Unactive'), null, ['class' => 'form-control']) }}
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
