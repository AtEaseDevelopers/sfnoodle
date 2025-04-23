<!-- Code Field -->
<div class="form-group">
    {!! Form::label('code', 'Code:') !!}
    <p>{{ $company->code }}</p>
</div>

<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $company->name }}</p>
</div>

<!-- Ssm Field -->
<div class="form-group">
    {!! Form::label('ssm', 'Ssm:') !!}
    <p>{{ $company->ssm }}</p>
</div>

<!-- Address1 Field -->
<div class="form-group">
    {!! Form::label('address1', 'Address1:') !!}
    <p>{{ $company->address1 }}</p>
</div>

<!-- Address2 Field -->
<div class="form-group">
    {!! Form::label('address2', 'Address2:') !!}
    <p>{{ $company->address2 }}</p>
</div>

<!-- Address3 Field -->
<div class="form-group">
    {!! Form::label('address3', 'Address3:') !!}
    <p>{{ $company->address3 }}</p>
</div>

<!-- Address4 Field -->
<div class="form-group">
    {!! Form::label('address4', 'Address4:') !!}
    <p>{{ $company->address4 }}</p>
</div>

<!-- Group Id Field -->
<div class="form-group">
    {!! Form::label('group_id', 'Group Id:') !!}
    <p>{{ $company->group->description }}</p>
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