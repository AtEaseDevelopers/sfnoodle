<!-- Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('code', 'Code:') !!}<span class="asterisk"> *</span>
    {!! Form::text('code', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255,'autofocus']) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}<span class="asterisk"> *</span>
    {!! Form::text('name', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Price Field -->
<div class="form-group col-sm-6">
    {!! Form::label('price', 'Price:') !!}<span class="asterisk"> *</span>
    {!! Form::number('price', null, ['class' => 'form-control','step'=> '0.01']) !!}
</div>

<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', 'Type:') !!}
    {{ Form::select('type', array(0 => 'Ice'), null, ['class' => 'form-control']) }}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {{ Form::select('status', array(1 => 'Active', 0 => 'Unactive'), null, ['class' => 'form-control']) }}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
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