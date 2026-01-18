@extends('layouts.app')

@section('content')
     <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('drivers.index') }}">Agents</a>
            </li>
            <li class="breadcrumb-item active">{{ __('drivers.detail') }}</li>
     </ol>
     <div class="container-fluid">
          <div class="animated fadeIn">
                 @include('flash::message')
                 @include('coreui-templates::common.errors')
                 <div class="row">
                     <div class="col-lg-12">
                         <div class="card">
                             <div class="card-header">
                                 <strong>{{ __('drivers.detail') }}</strong>
                                  <a href="{{ route('drivers.index') }}" class="btn btn-light">Back</a>
                             </div>
                             <div class="card-body">
                                 @include('drivers.show_fields')
                             </div>
                         </div>
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
    </script>
@endpush