@extends('layouts.app')

@section('content')
     <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('salesInvoiceDetails.index') }}">Sales Invoice Details</a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
     </ol>
     <div class="container-fluid">
          <div class="animated fadeIn">
                 @include('coreui-templates::common.errors')
                 <div class="row">
                     <div class="col-lg-12">
                         <div class="card">
                             <div class="card-header">
                                 <strong>Detail</strong>
                                  <a href="{{ route('salesInvoiceDetails.index') }}" class="btn btn-light">Back</a>
                             </div>
                             <div class="card-body">
                                 @include('sales_invoice_details.show_fields')
                             </div>
                         </div>
                     </div>
                 </div>
          </div>
    </div>
@endsection