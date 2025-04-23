<!-- EmployeeId Field -->
<div class="form-group">
    {!! Form::label('Employeeid', 'Employee ID:') !!}
    <p>{{ $supervisor->employeeid }}</p>
</div>

<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $supervisor->name }}</p>
</div>

<!-- Ic Field -->
<div class="form-group">
    {!! Form::label('ic', 'IC:') !!}
    <p>{{ $supervisor->ic }}</p>
</div>

<!-- Phone Field -->
<div class="form-group">
    {!! Form::label('phone', 'Phone:') !!}
    <p>{{ $supervisor->phone }}</p>
</div>

{{-- <!-- CommissionRate Field -->
<div class="form-group">
    {!! Form::label('phone', 'Commission Rate:') !!}
    <p>{{ $supervisor->commissionrate }}</p>
</div> --}}

<!-- bankdetails1 Field -->
<div class="form-group">
    {!! Form::label('bankdetails1', 'Bank Details 1:') !!}
    <p>{{ $supervisor->bankdetails1 }}</p>
</div>

<!-- bankdetails2 Field -->
<div class="form-group">
    {!! Form::label('bankdetails2', 'Bank Details 2:') !!}
    <p>{{ $supervisor->bankdetails2 }}</p>
</div>

<!-- 1vaccine Field -->
<div class="form-group">
    {!! Form::label('firstvaccine', '1st Vaccine Date:') !!}
    <p>{{ $supervisor->firstvaccine }}</p>
</div>

<!-- 2vaccine Field -->
<div class="form-group">
    {!! Form::label('secondvaccine', '2nd Vaccine Date:') !!}
    <p>{{ $supervisor->secondvaccine }}</p>
</div>

<!-- temperature Field -->
<div class="form-group">
    {!! Form::label('temperature', 'Body Temperature:') !!}
    <p>{{ $supervisor->temperature }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $supervisor->status == 1 ? "Active" : "Unactive" }}</p>
</div>

<!-- remark Field -->
<div class="form-group">
    {!! Form::label('remark', 'Remark:') !!}
    <p>{{ $supervisor->remark }}</p>
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

