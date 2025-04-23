<!-- Code Field -->
<div class="form-group">
    {!! Form::label('code', 'Code:') !!}
    <p>{{ $product->code }}</p>
</div>

<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $product->name }}</p>
</div>

<!-- Price Field -->
<div class="form-group">
    {!! Form::label('price', 'Price:') !!}
    <p>{{ number_format($product->price,2) }}</p>
</div>

<!-- Type Field -->
<div class="form-group">
    {!! Form::label('type', 'Type:') !!}
    @switch($product->type)
        @case(1)
            <p>Coffee</p>
            @break
        @case(2)
            <p>Tea</p>
            @break
        @case(3)
            <p>Cocoa</p>
            @break
        @case(4)
            <p>Ice</p>
            @break
        @default
            <p>Other</p>
    @endswitch
    <!--<p>{{ $product->status == 1 ? "Ice" : "Other" }}</p>-->
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $product->status == 1 ? "Active" : "Unactive" }}</p>
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
