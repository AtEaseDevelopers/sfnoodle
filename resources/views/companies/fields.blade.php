<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', 'Code:') !!}<span class="asterisk"> *</span>
    {!! Form::text('code', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}<span class="asterisk"> *</span>
    {!! Form::text('name', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Ssm Field -->
<div class="form-group col-sm-6">
    {!! Form::label('ssm', 'Ssm:') !!}<span class="asterisk"> *</span>
    {!! Form::text('ssm', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Address1 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address1', 'Address1:') !!}
    {!! Form::text('address1', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Address2 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address2', 'Address2:') !!}
    {!! Form::text('address2', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Address3 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address3', 'Address3:') !!}
    {!! Form::text('address3', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Address4 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('address4', 'Address4:') !!}
    {!! Form::text('address4', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Group Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('group_id', 'Group:') !!}<span class="asterisk"> *</span>
    {!! Form::select('group_id', $groups, explode(",",$company->group_id ?? ""), ['class' => 'selectpicker form-control', 'placeholder' => 'Select Group']) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('companies.index') }}" class="btn btn-secondary">Cancel</a>
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
