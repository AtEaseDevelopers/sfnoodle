@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
          <li class="breadcrumb-item">
             <a href="{!! route('invoices.index') !!}">{{ __('invoices.invoices') }}</a>
          </li>
          <li class="breadcrumb-item active">{{ __('invoices.edit') }}</li>
        </ol>
    <div class="container-fluid">
         <div class="animated fadeIn">
             @include('coreui-templates::common.errors')
             <div class="row">
                 <div class="col-lg-12">
                      <div class="card">
                            <div class="card-header">
                                <i class="fa fa-edit fa-lg"></i>
                                <strong>{{ __('invoices.edit_invoice') }}</strong>
                            </div>
                            <div class="card-body">
                            {!! Form::model($invoice, ['route' => ['invoices.update', encrypt($invoice->id)], 'method' => 'PUT', 'id' => 'invoiceForm', 'enctype' => 'multipart/form-data']) !!}

                              @include('invoices.fields')

                              {!! Form::close() !!}
                            </div>
                            
                            @if(isset($invoice) && $invoice->status == \App\Models\Invoice::STATUS_COMPLETED)
                            <div class="form-group col-sm-12 mt-4">
                                <hr>
                                <div class="alert alert-warning">
                                    <h5><i class="fa fa-exclamation-triangle"></i> Cancel Invoice</h5>
                                    <p class="mb-2">
                                        This action will cancel the invoice. 
                                        @if($invoice->paymentterm == 'Cash')
                                            <br>Associated payment records will also be cancelled.
                                        @endif
                                        @if($invoice->is_driver)
                                            <br>Inventory balance will be restored to the driver.
                                        @endif
                                    </p>
                                    
                                    <!-- Cancel Invoice Form -->
                                    <form method="POST" action="{{ route('invoices.cancelInvoice', encrypt($invoice->id)) }}" id="cancelInvoiceForm">
                                        @csrf
                                        @method('POST')
                                        
                                        <div class="form-group">
                                            <label for="cancellation_reason">Cancellation Reason (Optional):</label>
                                            <textarea name="cancellation_reason" id="cancellation_reason" 
                                                    class="form-control" rows="2" 
                                                    placeholder="Enter reason for cancellation..."></textarea>
                                        </div>
                                        
                                        <button type="button" class="btn btn-danger" id="cancelInvoiceBtn">
                                            <i class="fa fa-times-circle"></i> Cancel Invoice
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @endif
                        </div>
                    </div>
                </div>
         </div>
    </div>
    
@endsection