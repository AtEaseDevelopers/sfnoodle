<!-- Product Id Field -->
<div class="form-group col-sm-6"><span class="asterisk"> *</span>
    {!! Form::label('product_id', 'Product:') !!}
    {!! Form::select('product_id', $productItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Product...','autofocus']) !!}
</div>


<!-- Customer Id Field -->
<div class="form-group col-sm-6"><span class="asterisk"> *</span>
    {!! Form::label('customer_id', 'Customer:') !!}
    {!! Form::select('customer_id', $customerItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Customer...']) !!}
</div>


<!-- Price Field -->
<div class="form-group col-sm-6">
    {!! Form::label('price', 'Price:') !!}
    {!! Form::number('price', null, ['class' => 'form-control','step'=>'0.01','min'=>'0']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {{ Form::select('status', array(1 => 'Active', 0 => 'Unactive'), null, ['class' => 'form-control']) }}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('specialPrices.index') }}" class="btn btn-secondary">Cancel</a>
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