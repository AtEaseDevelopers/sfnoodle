{!! Form::open(['route' => ['invoicePayments.destroy', encrypt($id)], 'method' => 'delete']) !!}
<div class='btn-group'>
    @can('paymentapprove')
      @if($status == 0)
         <a href="#" onclick="check('{{encrypt($id)}}')" class='btn btn-ghost-success'>
            <i class="fa fa-check"></i>
         </a>
      @endif
    @endcan
    <a href="{{ route('invoicePayments.show', encrypt($id)) }}" class='btn btn-ghost-primary'>
       <i class="fa fa-eye"></i>
    </a>
    @php
        $invoice = \Illuminate\Support\Facades\DB::table('invoices')
        ->where('id', $invoice_id)
        ->first();

        $tripId = \Illuminate\Support\Facades\DB::table('drivers')
        ->where('trip_id', $invoice->trip_id)
        ->exists();
    @endphp
    
    @if($tripId)
    <a href="{{ route('invoicePayments.edit', encrypt($id)) }}" class='btn btn-ghost-info'>
       <i class="fa fa-edit"></i>
    </a>
    @endif

    @if($attachment)
        @php
            $fileUrl = asset('/' . $attachment);
            $fileExtension = pathinfo($attachment, PATHINFO_EXTENSION);
            $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
        @endphp
        
        <button type="button" class="btn btn-ghost-primary view-attachment" 
                data-toggle="modal" data-target="#attachmentModal"
                data-file="{{ $fileUrl }}"
                data-filename="{{ basename($attachment) }}"
                data-filetype="image">
            <i class="fa fa-print"></i>
        </button>
    @endif
    @if($tripId)
    {!! Form::button('<i class="fa fa-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-ghost-danger',
        'onclick' => "return confirm('".trans('invoice_payments.are_you_sure_to_delete_the_payment')."')"   
    ]) !!}
    @endif
</div>
{!! Form::close() !!}