@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
          <li class="breadcrumb-item">
             <a href="{!! route('customer_group.index') !!}">{{ __('Customer Group') }}</a>
          </li>
          <li class="breadcrumb-item active">{{ __('customer_group.edit') }}</li>
        </ol>
    <div class="container-fluid">
         <div class="animated fadeIn">
             @include('coreui-templates::common.errors')
             <div class="row">
                 <div class="col-lg-12">
                      <div class="card">
                          <div class="card-header">
                              <i class="fa fa-edit fa-lg"></i>
                              <strong>{{ __('customer_group.edit_customer_group') }}</strong>
                              <a href="{{ route('customer_group.index') }}" class="btn btn-light float-right">{{ __('Back') }}</a>
                          </div>
                          <div class="card-body">
                              {!! Form::model($customerGroup, ['route' => ['customer_group.update', Crypt::encrypt($customerGroup->id)], 'method' => 'patch']) !!}

                              @include('customer_group.fields')

                              {!! Form::close() !!}
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
            if (e.key === "Escape") {
                window.location.href = "{{ route('customer_group.index') }}";
            }
        });
        $(document).ready(function () {
            HideLoad();
        });
    </script>
@endpush