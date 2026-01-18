@extends('layouts.app')

@section('content')
     <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('assigns.index') }}">{{ __('assign.assigns') }}</a>
            </li>
            <li class="breadcrumb-item active">{{ __('assign.detail') }}</li>
     </ol>
     <div class="container-fluid">
          <div class="animated fadeIn">
                 @include('coreui-templates::common.errors')
                 <div class="row">
                     <div class="col-lg-12">
                         <div class="card">
                             <div class="card-header">
                                 <strong>{{ __('assign.detail') }}</strong>
                                  <div class="float-right">
                                      <a href="{{ route('assigns.edit', Crypt::encrypt($assign->id)) }}" class="btn btn-primary">
                                          <i class="fa fa-edit"></i> {{ __('assign.edit') }}
                                      </a>
                                      <a href="{{ route('assigns.index') }}" class="btn btn-light">
                                          <i class="fa fa-arrow-left"></i> {{ __('Back') }}
                                      </a>
                                  </div>
                             </div>
                             <div class="card-body">
                                 @include('assigns.show_fields')
                             </div>
                         </div>
                     </div>
                 </div>
          </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            HideLoad();
        });
    </script>
@endpush