<!-- Product Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('product_id', 'Product:') !!}<span class="asterisk"> *</span>
    {!! Form::select('product_id', $productItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Product...','autofocus']) !!}
</div>


<!-- Customer Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('customer_id', 'Customer:') !!}<span class="asterisk"> *</span>
    {!! Form::select('customer_id', $customerItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Customer...']) !!}
</div>


<!-- Quantity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('quantity', 'Quantity:') !!}<span class="asterisk"> *</span>
    {!! Form::number('quantity', null, ['class' => 'form-control','min' => 0]) !!}
</div>

<!-- Free Product Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('free_product_id', 'Free Product:') !!}<span class="asterisk"> *</span>
    {!! Form::select('free_product_id', $productItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Free Product...']) !!}
</div>


<!-- Free Quantity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('free_quantity', 'Free Quantity:') !!}<span class="asterisk"> *</span>
    {!! Form::text('free_quantity', null, ['class' => 'form-control']) !!}
</div>

<!-- Startdate Field -->
<div class="form-group col-sm-6">
    {!! Form::label('startdate', 'Start Date:') !!}<span class="asterisk"> *</span>
    {!! Form::text('startdate', null, ['class' => 'form-control','id'=>'startdate']) !!}
</div>

@push('scripts')
   <script type="text/javascript">
           $('#startdate').datetimepicker({
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


<!-- Enddate Field -->
<div class="form-group col-sm-6">
    {!! Form::label('enddate', 'End Date:') !!}<span class="asterisk"> *</span>
    {!! Form::text('enddate', null, ['class' => 'form-control','id'=>'enddate']) !!}
</div>

@push('scripts')
   <script type="text/javascript">
           $('#enddate').datetimepicker({
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


<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {{ Form::select('status', array(1 => 'Active', 0 => 'Unactive'), null, ['class' => 'form-control']) }}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('focs.index') }}" class="btn btn-secondary">Cancel</a>
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