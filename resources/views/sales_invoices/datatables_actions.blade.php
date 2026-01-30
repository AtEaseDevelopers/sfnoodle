{!! Form::open(['route' => ['salesInvoices.destroy', encrypt($id)], 'method' => 'delete']) !!}
<div class='btn-group'>
    <a href="{{ route('salesInvoice.print', ['id' => encrypt($id), 'function' => 'view'] ) }}" class='btn btn-ghost-primary' target="_blank" title="Print">
       <i class="fa fa-print"></i>
    </a>
    <a href="{{ route('salesInvoices.show', encrypt($id)) }}" class='btn btn-ghost-success' title="View">
       <i class="fa fa-eye"></i>
    </a>
      @php
         $tripId = \Illuminate\Support\Facades\DB::table('drivers')
            ->where('trip_id', $trip_id)
            ->exists();
      @endphp

    @if($tripId)
    <a href="{{ route('salesInvoices.edit', encrypt($id)) }}" class='btn btn-ghost-info' title="Edit">
       <i class="fa fa-edit"></i>
    </a>
    @endif

   @if($status == 0)
      <!-- Add Convert to Invoice button with payment term data -->
      <a href="#" class='btn btn-ghost-warning convert-to-invoice-btn' 
         title="Convert to Invoice" 
         data-id="{{ encrypt($id) }}"
         data-paymentterm="{{ $paymentterm ?? '' }}">
         <i class="fa fa-exchange"></i>
      </a>
    @endif  
    @if($tripId)
    {!! Form::button('<i class="fa fa-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-ghost-danger',
        'onclick' => "return confirm('Are you sure to delete the sales invoice?')",
        'title' => 'Delete'
    ]) !!}
   @endif
</div>
{!! Form::close() !!}