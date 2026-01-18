@extends('layouts.app')

@section('content')
     <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('salesInvoices.index') }}">Sales Order</a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
     </ol>
     <div class="container-fluid">
          <div class="animated fadeIn">
                 @include('flash::message')
                 @include('coreui-templates::common.errors')
                 <div class="row">
                     <div class="col-lg-12">
                         <div class="card">
                             <div class="card-header">
                                 <strong>Details</strong>
                                  <a href="{{ route('salesInvoices.index') }}" class="btn btn-light">Back</a>
                             </div>
                             <div class="card-body">
                                 @include('sales_invoices.show_fields')
                             </div>
                         </div>
                     </div>
                 </div>
                 @if($salesInvoice->created_by)
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Created By:</strong> {{ $salesInvoice->creator_name }}
                            @if($salesInvoice->is_driver)
                                <span class="badge badge-info">Driver</span>
                            @else
                                <span class="badge badge-primary">User</span>
                            @endif
                        </div>
                    </div>
                @endif

                @if($salesInvoice->converted_to_invoice && $salesInvoice->invoice)
                    <div class="alert alert-success mt-3">
                        <h5><i class="fa fa-check-circle"></i> Converted to Invoice</h5>
                        <p>This sales order has been converted to invoice: 
                            <a href="{{ route('invoices.show', encrypt($salesInvoice->invoice->id)) }}">
                                {{ $salesInvoice->invoice->invoiceno }}
                            </a>
                        </p>
                    </div>
                @elseif($salesInvoice->canBeConvertedToInvoice())
                    <div class="alert alert-info mt-3">
                        <h5><i class="fa fa-info-circle"></i> Ready for Conversion</h5>
                        <p>This sales order can be converted to an invoice.</p>
                        
                        @if($salesInvoice->paymentterm == 'Cash') <!-- Cash -->
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#cashPaymentModal">
                                <i class="fa fa-exchange-alt"></i> Convert to Invoice (Cash Payment)
                            </button>
                        @else
                            <form action="{{ route('salesInvoices.convertToInvoice') }}" method="POST" class="d-inline" id="convertForm">
                                @csrf
                                <input type="hidden" name="id" value="{{ encrypt($salesInvoice->id) }}">
                                <button type="button" class="btn btn-primary btn-sm" id="convertNonCashBtn">
                                    <i class="fa fa-exchange-alt"></i> Convert to Invoice
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
                 <div class="row">
                     <div class="col-lg-12">
                         <div class="card">
                             <div class="card-header">
                                 <strong>Sales Order Detail</strong>
                                <a class="pull-right" href="{{ route('salesInvoices.detail', Crypt::encrypt($id)) }}"><i class="fa fa-plus-square fa-lg"></i></a>
                             </div>
                             <div class="card-body">
                                <table class="table table-striped table-bordered dataTable" width="100%" role="grid" style="width: 100%;">
                                    <thead>
                                        <tr role="row">
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Total Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($salesInvoiceDetails) == 0)
                                            <tr class="odd">
                                                <td valign="top" colspan="10" class="dataTables_empty">No matching records found</td>
                                            </tr>
                                        @endif
                                        @foreach($salesInvoiceDetails as $i=>$salesInvoiceDetail)
                                            @if( ($i+1) % 2 == 0 )

                                                <tr class="even">
                                                    <td>{{ $salesInvoiceDetail['product']['name'] }}</td>
                                                    <td>{{ $salesInvoiceDetail['quantity'] }}</td>
                                                    <td>{{ $salesInvoiceDetail['price'] }}</td>
                                                    <td>{{ $salesInvoiceDetail['totalprice'] }}</td>
                                                    <td>
                                                    {!! Form::open(['route' => ['salesInvoices.deletedetail', Crypt::encrypt($salesInvoiceDetail['id'])], 'method' => 'delete']) !!}
                                                        <div class='btn-group'>
                                                            {!! Form::button('<i class="fa fa-trash"></i>', [
                                                                'type' => 'submit',
                                                                'class' => 'btn btn-ghost-danger',
                                                                'onclick' => "return confirm('Are you sure to delete the Sales Invoice Detail?')"
                                                            ]) !!}
                                                        </div>
                                                    {!! Form::close() !!}
                                                    </td>
                                                </tr>
                                            @else
                                                <tr class="odd">
                                                    <td>{{ $salesInvoiceDetail['product']['name'] }}</td>
                                                    <td>{{ $salesInvoiceDetail['quantity'] }}</td>
                                                    <td>{{ $salesInvoiceDetail['price'] }}</td>
                                                    <td>{{ $salesInvoiceDetail['totalprice'] }}</td>
                                                    <td>
                                                    {!! Form::open(['route' => ['salesInvoices.deletedetail', Crypt::encrypt($salesInvoiceDetail['id'])], 'method' => 'delete']) !!}
                                                        <div class='btn-group'>
                                                            {!! Form::button('<i class="fa fa-trash"></i>', [
                                                                'type' => 'submit',
                                                                'class' => 'btn btn-ghost-danger',
                                                                'onclick' => "return confirm('Are you sure to delete the Sales Invoice Detail?')"
                                                            ]) !!}
                                                        </div>
                                                    {!! Form::close() !!}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>

                             </div>
                         </div>
                     </div>
                 </div>
          </div>
    </div>
    <!-- Modal for Cash Payment Proof -->
<div class="modal fade" id="cashPaymentModal" tabindex="-1" role="dialog" aria-labelledby="cashPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cashPaymentModalLabel">Payment Proof Required</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>This sales order has <strong>Cash</strong> payment term. Please upload proof of payment before conversion.</p>
                
                <form id="cashPaymentForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="sales_invoice_id" value="{{ encrypt($salesInvoice->id) }}">
                    
                    <div class="form-group">
                        <label for="attachment">Payment Proof (Image/PDF)</label>
                        <input type="file" class="form-control-file" id="attachment" name="attachment" required accept=".jpg,.jpeg,.png,.pdf,.gif">
                        <small class="form-text text-muted">Upload proof of payment (Max: 5MB)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_amount">Amount</label>
                        <input type="number" class="form-control" id="payment_amount" name="amount" 
                               value="{{ $salesInvoice->formatted_total  ?? 0 }}" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_remark">Remark (Optional)</label>
                        <textarea class="form-control" id="payment_remark" name="remark" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitCashPayment">Convert with Payment Proof</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if(e.altKey && e.keyCode == 78){
                $('.card .card-header a')[1].click();
            }
        });
        $(document).ready(function() {
        // Non-cash conversion confirmation
        $('#convertNonCashBtn').click(function(e) {
            e.preventDefault();
            
            $.confirm({
                title: 'Confirm Conversion',
                content: 'Are you sure you want to convert this Sales Order to an invoice?',
                buttons: {
                    confirm: {
                        text: 'Convert',
                        btnClass: 'btn-primary',
                        action: function() {
                            $('#convertForm').submit();
                        }
                    },
                    cancel: {
                        text: 'Cancel',
                        btnClass: 'btn-secondary'
                    }
                }
            });
        });
        
        // Cash payment form submission
        $('#submitCashPayment').click(function(e) {
            e.preventDefault();
            
            var formData = new FormData($('#cashPaymentForm')[0]);
            
            // Show loading
            ShowLoad();
            
            $.ajax({
                url: "{{ route('salesInvoices.convertWithPayment') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    HideLoad();
                    
                    if (response.success) {
                        $('#cashPaymentModal').modal('hide');
                        
                        toastr.success(response.message, 'Success');
                        
                        // Refresh page after 2 seconds
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        toastr.error(response.message, 'Error');
                    }
                },
                error: function(xhr) {
                    HideLoad();
                    
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = 'An error occurred.';
                    
                    if (errors && errors.attachment) {
                        errorMessage = errors.attachment[0];
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    toastr.error(errorMessage, 'Error');
                }
            });
        });
    });
    </script>
@endpush