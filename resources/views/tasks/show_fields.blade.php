<!-- Date Field -->
<div class="form-group">
    {!! Form::label('date', 'Date:') !!}
    <p>{{ $task->date }}</p>
</div>

<!-- Driver Id Field -->
<div class="form-group">
    {!! Form::label('driver_id', 'Driver:') !!}
    <p>{{ $task->driver->name }}</p>
</div>

<!-- Customer Id Field -->
<div class="form-group">
    {!! Form::label('customer_id', 'Customer:') !!}
    <p>{{ $task->customer->company }}</p>
</div>

<!-- Sequence Field -->
<div class="form-group">
    {!! Form::label('sequence', 'Sequence:') !!}
    <p>{{ $task->sequence }}</p>
</div>

<!-- Invoice Id Field -->
<div class="form-group">
    {!! Form::label('invoice_id', 'Invoice:') !!}
    <p>{{ $task->invoice->invoiceno ?? "" }}</p>
</div>

<!-- Status Field -->
<div class="form-group">
    {!! Form::label('status', 'Status:') !!}
    @if($task->status == 0)
        <p>New</p>
    @elseif($task->status == 1)
        <p>In-Progress</p>
    @elseif($task->status == 8)
        <p>Completed</p>
    @elseif($task->status == 9)
        <p>Cancelled</p>
    @endif
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