@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{!! route('customer_group.index') !!}">{{ __('Customer Groups') }}</a>
        </li>
        <li class="breadcrumb-item active">{{ __('View Details') }}</li>
    </ol>
    <div class="container-fluid">
        <div class="animated fadeIn">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-eye fa-lg"></i>
                            <strong>{{ __('Show Customer Group') }}</strong>
                            <div class="float-right">
                                <a href="{{ route('customer_group.edit', Crypt::encrypt($customerGroup->id)) }}" class="btn btn-primary">
                                    <i class="fa fa-edit"></i> {{ __('customer_group.edit') }}
                                </a>
                                <a href="{{ route('customer_group.index') }}" class="btn btn-secondary">
                                    </i> {{ __('Back') }}
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('customer_group.show_fields')
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