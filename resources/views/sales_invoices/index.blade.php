@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">Sales Order</li>
    </ol>
    <div class="container-fluid">
        <div class="animated fadeIn">
             @include('flash::message')
             <div class="row">
                 <div class="col-lg-12">
                     <div class="card">
                         <div class="card-header">
                             <i class="fa fa-align-justify"></i>
                             Sales Order
                             <a class="pull-right" href="{{ route('salesInvoices.create') }}"><i class="fa fa-plus-square fa-lg"></i></a>
                             <!-- <a class="pull-right text-danger pr-2" id="massdelete" href="#" alt="Mass delete"><i class="fa fa-trash fa-lg"></i></a>
                             <a class="pull-right text-success pr-2" id="massactive" href="#" alt="Mass active"><i class="fa fa-check fa-lg"></i></a> -->
                         
                             <!--<a class="pull-right pr-2" id="masssyncxero" href="#" alt="Mass Sync to Xero"><i class="fa fa-refresh fa-lg"></i></a>-->
                         </div>
                         <div class="card-body">
                             @include('sales_invoices.table')
                              <div class="pull-right mr-3">
                                     
                              </div>
                         </div>
                     </div>
                  </div>
             </div>
         </div>
    </div>
    
    <!-- Modal for Cash Payment Proof (INDEX PAGE) -->
    <div class="modal fade" id="cashPaymentModalIndex" tabindex="-1" role="dialog" aria-labelledby="cashPaymentModalLabelIndex" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cashPaymentModalLabelIndex">Payment Proof Required</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>This sales invoice has <strong>Cash</strong> payment term. Please upload proof of payment before conversion.</p>
                    
                    <form id="cashPaymentFormIndex" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="sales_invoice_id" id="cashSalesInvoiceId">
                        
                        <div class="form-group">
                            <label for="attachment_index">Payment Proof (Image/PDF)*</label>
                            <input type="file" class="form-control-file" id="attachment_index" name="attachment" required accept=".jpg,.jpeg,.png,.pdf,.gif">
                            <small class="form-text text-muted">Upload proof of payment (Max: 5MB)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_amount_index">Amount*</label>
                            <input type="number" class="form-control" id="payment_amount_index" name="amount" step="0.01" min="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_remark_index">Remark (Optional)</label>
                            <textarea class="form-control" id="payment_remark_index" name="remark" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitCashPaymentIndex">Convert with Payment Proof</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if(e.altKey && e.keyCode == 78){
                $('.card .card-header a')[0].click();
            } 
        });
        
        // ====== Convert to Invoice Function for DATATABLE ACTION BUTTON ======
        $(document).on("click", ".convert-to-invoice-btn", function(e){
            e.preventDefault();
            var id = $(this).data('id');
            var row = $(this).closest('tr');
            var paymentTerm = $(this).data('paymentterm'); // Get payment term from button
            console.log(paymentTerm);
            // Show modal for CASH payments
            if (paymentTerm === 'Cash') {
                // Set the sales invoice ID in the modal
                $('#cashSalesInvoiceId').val(id);
                
                // Get the total amount from the row
                var totalAmount = row.find('td:nth-child(5)').text() || row.find('td:nth-child(4)').text(); // Adjust column index based on your table
                $('#payment_amount_index').val(parseFloat(totalAmount) || 0);
                
                // Show the modal
                $('#cashPaymentModalIndex').modal('show');
            } else {
                // For NON-CASH payments, show confirmation and convert directly
                var confirmMessage = 'Confirm to convert this sales invoice to an invoice?';
                
                $.confirm({
                    title: 'Convert to Invoice',
                    content: confirmMessage,
                    buttons: {
                        Confirm: {
                            text: 'Convert',
                            btnClass: 'btn-primary',
                            action: function() {
                                convertSingleToInvoice(id, row, paymentTerm);
                            }
                        },
                        Cancel: {
                            text: 'Cancel',
                            btnClass: 'btn-gray'
                        }
                    }
                });
            }
        });

        // Function to handle NON-CASH conversion (Credit, Online BankIn, E-wallet, Cheque)
        function convertSingleToInvoice(id, row, paymentTerm) {
            ShowLoad();
            $.ajax({
                url: "{{ route('salesInvoices.convertToInvoice') }}",
                type: "POST",
                data: {
                    id: id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    HideLoad();
                    
                    if(response.success) {
                        let message = response.message;
                        let invoiceLink = '';
                        
                        if(response.invoice_id) {
                            invoiceLink = ' <a href="{{ url("invoices") }}/' + response.invoice_id + '/edit" target="_blank">View Invoice</a>';
                        }
                        
                        toastr.success(message + invoiceLink, 'Conversion Successful', {
                            showEasing: "swing", 
                            hideEasing: "linear", 
                            showMethod: "fadeIn", 
                            hideMethod: "fadeOut", 
                            positionClass: "toast-bottom-right", 
                            timeOut: 5000, 
                            allowHtml: true 
                        });
                        
                        // Optionally hide or update the row
                        row.fadeOut(300, function() {
                            $('.buttons-reload').click();
                        });
                    } else {
                        noti('e','Conversion Failed', response.message);
                    }
                },
                error: function(error) {
                    HideLoad();
                    let errorMsg = 'An error occurred during conversion.';
                    if(error.responseJSON && error.responseJSON.message) {
                        errorMsg = error.responseJSON.message;
                    }
                    noti('e','Conversion Failed', errorMsg);
                }
            });
        }

        // Handle CASH payment form submission in INDEX page modal
        $('#submitCashPaymentIndex').click(function(e) {
            e.preventDefault();
            
            var formData = new FormData($('#cashPaymentFormIndex')[0]);
            
            // Validate amount
            const amount = parseFloat($('#payment_amount_index').val());
            
            if (amount <= 0) {
                toastr.error('Amount must be greater than 0', 'Error');
                return;
            }
            
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
                        $('#cashPaymentModalIndex').modal('hide');
                        
                        toastr.success(response.message, 'Success');
                        
                        // Reload datatable after 1 second
                        setTimeout(function() {
                            $('.buttons-reload').click();
                        }, 1000);
                    } else {
                        toastr.error(response.message, 'Error');
                    }
                },
                error: function(xhr) {
                    HideLoad();
                    
                    var errorMessage = 'An error occurred during conversion.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    toastr.error(errorMessage, 'Error');
                }
            });
        });
    </script>
@endpush