<!-- Lorry Id Field -->
<div class="form-group">
    {!! Form::label('lorry_id', 'Lorry:') !!}
    <p>{{ $servicedetails->lorry->lorryno }}</p>
</div>

<!-- Type Field -->
<div class="form-group">
    {!! Form::label('type', 'Type:') !!}
    <p>{{ $servicedetails->type }}</p>
</div>

<!-- Date Field -->
<div class="form-group">
    {!! Form::label('date', 'Date:') !!}
    <p>{{ $servicedetails->date }}</p>
</div>

<!-- Nextdate Field -->
<div class="form-group">
    {!! Form::label('nextdate', 'Nextdate:') !!}
    <p>{{ $servicedetails->nextdate }}</p>
</div>

<!-- Amount Field -->
<div class="form-group">
    {!! Form::label('amount', 'Amount:') !!}
    <p>{{ number_format($servicedetails->amount,2) }}</p>
</div>

<!-- Remark Field -->
<div class="form-group">
    {!! Form::label('remark', 'Remark:') !!}
    <p>{{ $servicedetails->remark }}</p>
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

