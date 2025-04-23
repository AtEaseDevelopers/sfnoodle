<!-- EmployeeId Field -->
<div class="form-group col-sm-6">
    {!! Form::label('employeeid', 'Employee ID:') !!}<span class="asterisk"> *</span>
    {!! Form::text('employeeid', null, ['class' => 'form-control','maxlength' => 20,'autofocus']) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}<span class="asterisk"> *</span>
    {!! Form::text('name', null, ['class' => 'form-control','maxlength' => 255]) !!}
</div>

<!-- Ic Field -->
<div class="form-group col-sm-6">
    {!! Form::label('ic', 'IC:') !!}
    {!! Form::text('ic', null, ['class' => 'form-control','maxlength' => 20]) !!}
</div>

<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone', 'Phone:') !!}
    {!! Form::text('phone', null, ['class' => 'form-control','maxlength' => 255]) !!}
</div>

{{-- <!-- CommissionRate Field -->
<div class="form-group col-sm-6">
    {!! Form::label('commissionrate', 'Commission Rate:') !!}<span class="asterisk"> *</span>
    {!! Form::number('commissionrate', null, ['class' => 'form-control','step'=>'0.01','min'=>'0', 'required'=> 'true']) !!}
</div> --}}

<!-- bankdetails1 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('bankdetails1', 'Bank Details 1:') !!}
    {!! Form::text('bankdetails1', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- bankdetails2 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('bankdetails2', 'Bank Details 2:') !!}
    {!! Form::text('bankdetails2', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- firstvaccine Field
<div class="form-group col-sm-6">
    {!! Form::label('firstvaccine', '1st Vaccine Date:') !!}
    {!! Form::text('firstvaccine', null, ['class' => 'form-control','id'=>'firstvaccine']) !!}
</div>

@push('scripts')
   <script type="text/javascript">
           $('#firstvaccine').datetimepicker({
               format: 'DD-MM-YYYY',
               useCurrent: true,
               icons: {
                    time: "fa fa-clock-o",
                    date: "fa fa-calendar",
                    up: "fa fa-arrow-up",
                    down: "fa fa-arrow-down",
                    previous: "fa fa-chevron-left",
                    next: "fa fa-chevron-right",
                    today: "fa fa-clock-o",
                    clear: "fa fa-trash-o"
               },
               sideBySide: true
           })
       </script>
@endpush -->

<!-- secondvaccine Field
<div class="form-group col-sm-6">
    {!! Form::label('secondvaccine', '2nd Vaccine Date:') !!}
    {!! Form::text('secondvaccine', null, ['class' => 'form-control','id'=>'secondvaccine']) !!}
</div>

@push('scripts')
   <script type="text/javascript">
           $('#secondvaccine').datetimepicker({
               format: 'DD-MM-YYYY',
               useCurrent: true,
               icons: {
                    time: "fa fa-clock-o",
                    date: "fa fa-calendar",
                    up: "fa fa-arrow-up",
                    down: "fa fa-arrow-down",
                    previous: "fa fa-chevron-left",
                    next: "fa fa-chevron-right",
                    today: "fa fa-clock-o",
                    clear: "fa fa-trash-o"
               },
               sideBySide: true
           })
       </script>
@endpush -->

<!-- temperature Field 
<div class="form-group col-sm-6">
    {!! Form::label('temperature', 'Body Temperature:') !!}
    {!! Form::number('temperature', null, ['class' => 'form-control','step'=>'0.1','min'=>'0']) !!}
</div>
-->
<!-- permitdate Field  -->
<div class="form-group col-sm-6">
    {!! Form::label('permitdate', 'Permit Date:') !!}
    {!! Form::text('permitdate', null, ['class' => 'form-control','id'=>'permitdate']) !!}
</div>
@push('scripts')
   <script type="text/javascript">
           $('#permitdate').datetimepicker({
               format: 'DD-MM-YYYY',
               useCurrent: true,
               icons: {
                    time: "fa fa-clock-o",
                    date: "fa fa-calendar",
                    up: "fa fa-arrow-up",
                    down: "fa fa-arrow-down",
                    previous: "fa fa-chevron-left",
                    next: "fa fa-chevron-right",
                    today: "fa fa-clock-o",
                    clear: "fa fa-trash-o"
               },
               sideBySide: true
           })
       </script>
@endpush

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}<span class="asterisk"> *</span>
    {{ Form::select('status', array(1 => 'Active', 0 => 'Unactive'), null, ['class' => 'form-control']) }}
</div>

<!-- Remark Field -->
<div class="form-group col-sm-6">
    {!! Form::label('remark', 'Remark:') !!}
    {!! Form::text('remark', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('kelindans.index') }}" class="btn btn-secondary">Cancel</a>
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
