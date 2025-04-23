<!-- Lorryno Field -->
<div class="form-group">
    {!! Form::label('lorryno', 'Lorry#:') !!}
    <p>{{ $lorry->lorryno }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $lorry->status == 1 ? "Active" : "Unactive" }}</p>
</div>

<!-- remark Field -->
<div class="form-group">
    {!! Form::label('remark', 'Remark:') !!}
    <p>{{ $lorry->remark }}</p>
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