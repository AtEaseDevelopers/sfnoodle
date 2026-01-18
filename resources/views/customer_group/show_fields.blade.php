<!-- Name Field -->
<div class="form-group row">
    <div class="col-sm-3 text-right">
        <strong>{{ __('Name') }}:</strong>
    </div>
    <div class="col-sm-9">
        <p class="form-control-plaintext">{{ $customerGroup->name }}</p>
    </div>
</div>

<!-- Description Field -->
<div class="form-group row">
    <div class="col-sm-3 text-right">
        <strong>{{ __('customer_group.description') }}:</strong>
    </div>
    <div class="col-sm-9">
        <p class="form-control-plaintext">{{ $customerGroup->description ?? 'N/A' }}</p>
    </div>
</div>

<!-- Customers with Sequence Field -->
<div class="form-group row">
    <div class="col-sm-3 text-right">
        <strong>Customers (with Sequence):</strong>
    </div>
    <div class="col-sm-9">
        @php
            // Get customers sorted by sequence
            $sortedCustomers = $customerGroup->getCustomersSortedBySequence();
        @endphp
        
        @if($sortedCustomers->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th width="10%" class="text-center">#</th>
                            <th width="15%" class="text-center">Sequence</th>
                            <th width="75%">Customer Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sortedCustomers as $index => $customer)
                            <tr>
                                <td class="text-center align-middle">{{ $index + 1 }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-primary">{{ $customer->sequence ?? ($index + 1) }}</span>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $customer->company }}</strong>
                                        @if($customer->code)
                                            <span class="text-muted ml-2">({{ $customer->code }})</span>
                                        @endif
                                    </div>
                                    @if($customer->name)
                                        <div class="text-muted small">{{ $customer->name }}</div>
                                    @endif
                                    @if($customer->phone)
                                        <div class="text-muted small">
                                            <i class="fa fa-phone"></i> {{ $customer->phone }}
                                        </div>
                                    @endif
                                    @if($customer->paymentterm)
                                        <div class="text-muted small">
                                            <i class="fa fa-credit-card"></i> {{ ucfirst($customer->paymentterm) }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right">
                                <strong>Total Customers:</strong> {{ $sortedCustomers->count() }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Summary Information -->
            <div class="alert alert-info mt-3">
                <div class="row">
                    <div class="col-md-6">
                        <i class="fa fa-info-circle"></i> 
                        <strong>Sequence Rules:</strong>
                        <ul class="mb-0 mt-2 pl-3">
                            <li>Lower sequence numbers display first</li>
                            <li>Customers with same sequence are ordered by name</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <i class="fa fa-sort-numeric-asc"></i> 
                        <strong>Sequence Range:</strong>
                        <ul class="mb-0 mt-2 pl-3">
                            <li>Lowest sequence: {{ $sortedCustomers->min('sequence') ?? 1 }}</li>
                            <li>Highest sequence: {{ $sortedCustomers->max('sequence') ?? $sortedCustomers->count() }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        @else
            <p class="form-control-plaintext text-muted">{{ __('customer_group.no_customers_selected') }}</p>
        @endif
    </div>
</div>

<!-- Customer Count Field -->
<div class="form-group row">
    <div class="col-sm-3 text-right">
        <strong>{{ __('Customer Selected Count') }}:</strong>
    </div>
    <div class="col-sm-9">
        <p class="form-control-plaintext">
            {{ count($customerGroup->customer_ids ?? []) }}
        </p>
    </div>
</div>

<!-- Status Field -->
@if(isset($customerGroup->status))
<div class="form-group row">
    <div class="col-sm-3 text-right">
        <strong>{{ __('customer_group.status') }}:</strong>
    </div>
    <div class="col-sm-9">
        <p class="form-control-plaintext">
            @if($customerGroup->status)
                <span class="badge badge-success">{{ __('customer_group.active') }}</span>
            @else
                <span class="badge badge-danger">{{ __('customer_group.inactive') }}</span>
            @endif
        </p>
    </div>
</div>
@endif

<!-- Created At Field -->
<div class="form-group row">
    <div class="col-sm-3 text-right">
        <strong>{{ __('customer_group.created_at') }}:</strong>
    </div>
    <div class="col-sm-9">
        <p class="form-control-plaintext">
            {{ $customerGroup->created_at ? $customerGroup->created_at->format('d/m/Y H:i:s') : 'N/A' }}
        </p>
    </div>
</div>

<!-- Updated At Field -->
<div class="form-group row">
    <div class="col-sm-3 text-right">
        <strong>{{ __('customer_group.updated_at') }}:</strong>
    </div>
    <div class="col-sm-9">
        <p class="form-control-plaintext">
            {{ $customerGroup->updated_at ? $customerGroup->updated_at->format('d/m/Y H:i:s') : 'N/A' }}
        </p>
    </div>
</div>

<!-- Deleted At Field -->
@if($customerGroup->deleted_at)
<div class="form-group row">
    <div class="col-sm-3 text-right">
        <strong>{{ __('customer_group.deleted_at') }}:</strong>
    </div>
    <div class="col-sm-9">
        <p class="form-control-plaintext text-danger">
            {{ $customerGroup->deleted_at->format('d/m/Y H:i:s') }}
            <span class="badge badge-danger ml-2">{{ __('customer_group.deleted') }}</span>
        </p>
    </div>
</div>
@endif

@push('css')
    <style>
        .form-control-plaintext {
            padding-top: calc(.375rem + 1px);
            padding-bottom: calc(.375rem + 1px);
            margin-bottom: 0;
        }
        .table-sm th, .table-sm td {
            padding: 0.5rem;
        }
        .badge-primary {
            background-color: #007bff;
            font-size: 0.9em;
            min-width: 30px;
            padding: 0.25em 0.6em;
        }
        .alert-info {
            background-color: #f8f9fa;
            border-color: #e9ecef;
            color: #495057;
        }
        .alert-info ul {
            margin-bottom: 0;
        }
        .alert-info li {
            padding: 2px 0;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            HideLoad();
        });
    </script>
@endpush