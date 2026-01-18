@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
          <li class="breadcrumb-item">
             <a href="{!! route('salesInvoices.index') !!}">Sales Order</a>
          </li>
          <li class="breadcrumb-item active">Edit</li>
        </ol>
    <div class="container-fluid">
         <div class="animated fadeIn">
             @include('coreui-templates::common.errors')
             <div class="row">
                 <div class="col-lg-12">
                      <div class="card">
                          <div class="card-header">
                              <i class="fa fa-edit fa-lg"></i>
                              <strong>Edit Sales Order</strong>
                          </div>
                          <div class="card-body">
                            {!! Form::model($salesInvoice, ['route' => ['salesInvoices.update', encrypt($salesInvoice->id)], 'method' => 'patch', 'id' => 'salesInvoiceForm']) !!}
                                @php
                                    $isEdit = true;
                                @endphp
                              @include('sales_invoices.fields')

                              {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
         </div>
    </div>
@endsection