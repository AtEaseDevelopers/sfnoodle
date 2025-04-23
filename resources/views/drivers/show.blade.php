@extends('layouts.app')

@section('content')
     <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('drivers.index') }}">Driver</a>
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
                                  <a href="{{ route('drivers.index') }}" class="btn btn-light">Back</a>
                             </div>
                             <div class="card-body">
                                 @include('drivers.show_fields')
                             </div>
                         </div>
                     </div>
                 </div>
                 <div class="row">
                     <div class="col-lg-12">
                         <div class="card">
                             <div class="card-header">
                                 <strong>Customer</strong>
                                <a class="pull-right" href="{{ route('drivers.assign', Crypt::encrypt($id)) }}"><i class="fa fa-plus-square fa-lg"></i></a>
                             </div>
                             <div class="card-body">
                                <table class="table table-striped table-bordered dataTable" width="100%" role="grid" style="width: 100%;">
                                    <thead>
                                        <tr role="row">
                                            <th>Code</th>
                                            <th>Company</th>
                                            <th>Phone</th>
                                            <th>Address</th>
                                            <th>Sequence</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($assign) == 0)
                                            <tr class="odd">
                                                <td valign="top" colspan="10" class="dataTables_empty">No matching records found</td>
                                            </tr>
                                        @endif
                                        @foreach($assign as $i=>$ass)
                                            @if( ($i+1) % 2 == 0 )

                                                <tr class="even">
                                                    <td>{{ $ass['customer']['code'] }}</td>
                                                    <td>{{ $ass['customer']['company'] }}</td>
                                                    <td>{{ $ass['customer']['phone'] }}</td>
                                                    <td class=" truncate">{{ $ass['customer']['address'] }}</td>
                                                    <td>{{ $ass['sequence'] }}</td>
                                                    <td>
                                                    {!! Form::open(['route' => ['drivers.deleteassign', Crypt::encrypt($ass['id'])], 'method' => 'delete']) !!}
                                                        <div class='btn-group'>
                                                            {!! Form::button('<i class="fa fa-trash"></i>', [
                                                                'type' => 'submit',
                                                                'class' => 'btn btn-ghost-danger',
                                                                'onclick' => "return confirm('Are you sure to delete the Assign?')"
                                                            ]) !!}
                                                        </div>
                                                    {!! Form::close() !!}
                                                    </td>
                                                </tr>
                                            @else
                                                <tr class="odd">
                                                    <td>{{ $ass['customer']['code'] }}</td>
                                                    <td>{{ $ass['customer']['company'] }}</td>
                                                    <td>{{ $ass['customer']['phone'] }}</td>
                                                    <td class=" truncate">{{ $ass['customer']['address'] }}</td>
                                                    <td>{{ $ass['sequence'] }}</td>
                                                    <td>
                                                    {!! Form::open(['route' => ['drivers.deleteassign', Crypt::encrypt($ass['id'])], 'method' => 'delete']) !!}
                                                        <div class='btn-group'>
                                                            {!! Form::button('<i class="fa fa-trash"></i>', [
                                                                'type' => 'submit',
                                                                'class' => 'btn btn-ghost-danger',
                                                                'onclick' => "return confirm('Are you sure to delete the Assign?')"
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