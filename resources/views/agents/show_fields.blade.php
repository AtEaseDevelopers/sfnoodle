<!-- EmployeeId Field -->
<div class="form-group">
    {!! Form::label('Employeeid', 'Employee ID:') !!}
    <p>{{ $agent->employeeid }}</p>
</div>

<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $agent->name }}</p>
</div>

<!-- Ic Field -->
<div class="form-group">
    {!! Form::label('ic', 'IC:') !!}
    <p>{{ $agent->ic }}</p>
</div>

<!-- Phone Field -->
<div class="form-group">
    {!! Form::label('phone', 'Phone:') !!}
    <p>{{ $agent->phone }}</p>
</div>

{{-- <!-- CommissionRate Field -->
<div class="form-group">
    {!! Form::label('phone', 'Commission Rate:') !!}
    <p>{{ $agent->commissionrate }}</p>
</div> --}}

<!-- bankdetails1 Field -->
<div class="form-group">
    {!! Form::label('bankdetails1', 'Bank Details 1:') !!}
    <p>{{ $agent->bankdetails1 }}</p>
</div>

<!-- bankdetails2 Field -->
<div class="form-group">
    {!! Form::label('bankdetails2', 'Bank Details 2:') !!}
    <p>{{ $agent->bankdetails2 }}</p>
</div>

<!-- 1vaccine Field -->
<div class="form-group">
    {!! Form::label('firstvaccine', '1st Vaccine Date:') !!}
    <p>{{ $agent->firstvaccine }}</p>
</div>

<!-- 2vaccine Field -->
<div class="form-group">
    {!! Form::label('secondvaccine', '2nd Vaccine Date:') !!}
    <p>{{ $agent->secondvaccine }}</p>
</div>

<!-- temperature Field -->
<div class="form-group">
    {!! Form::label('temperature', 'Body Temperature:') !!}
    <p>{{ $agent->temperature }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $agent->status == 1 ? "Active" : "Unactive" }}</p>
</div>

<!-- remark Field -->
<div class="form-group">
    {!! Form::label('remark', 'Remark:') !!}
    <p>{{ $agent->remark }}</p>
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

