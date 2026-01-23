<div class="row">
    <!-- Driver Information -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-user-circle"></i> {{ __('Agent Information') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="40%"><strong>{{ __('Agent Code') }}:</strong></td>
                        <td>{{ $assign->driver->employeeid ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>{{ __('Agent Name') }}:</strong></td>
                        <td>{{ $assign->driver->name ?? 'N/A' }}</td>
                    </tr>
                    
                </table>
            </div>
        </div>
    </div>

    <!-- Customer Group Information -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-users"></i> {{ __('Customer Group') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="40%"><strong>{{ __('Group Name') }}:</strong></td>
                        <td>{{ $assign->customerGroup->name ?? 'N/A' }}</td>
                    </tr>
                    @if($assign->customerGroup && $assign->customerGroup->description)
                    <tr>
                        <td><strong>{{ __('Description') }}:</strong></td>
                        <td>{{ $assign->customerGroup->description }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>{{ __('Customer Count') }}:</strong></td>
                        <td>
                            <span class="badge badge-info">
                                {{ $assign->customerGroup ? count($assign->customerGroup->customer_ids ?? []) : 0 }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Customers List -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa fa-users"></i> {{ __('Customers in Group') }}
            <span class="badge badge-info">
                {{ $assign->customerGroup ? count($assign->customerGroup->customer_ids ?? []) : 0 }}
            </span>
        </h5>
    </div>
    <div class="card-body p-0">
        @if($assign->customerGroup && !empty($assign->customerGroup->customer_ids))
            @php
                // Extract just the customer IDs from the JSON structure
                $customerData = $assign->customerGroup->customer_ids;
                $customerIds = collect($customerData)->pluck('id')->toArray();
                
                $customers = \App\Models\Customer::whereIn('id', $customerIds)
                    ->select('id', 'code', 'company')
                    ->get();
            @endphp
            
            @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>{{ __('Company') }}</th>
                                <th>{{ __('Code') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr>
                                    <td>
                                        <strong>{{ $customer->company }}</strong>
                                    </td>
                                    <td>{{ $customer->code ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fa fa-user-times fa-2x mb-2"></i>
                    <p>{{ __('No Customer in group') }}</p>
                </div>
            @endif
        @else
            <div class="text-center text-muted py-4">
                <i class="fa fa-users fa-2x mb-2"></i>
                <p>{{ __('No Customer assigned') }}</p>
            </div>
        @endif
    </div>
</div>

<!-- Timestamps -->
<div class="row">
    <div class="col-md-6">
        <div class="small text-muted">
            <i class="fa fa-calendar-plus"></i> 
            <strong>{{ __('Created At') }}:</strong> 
            {{ $assign->created_at ? $assign->created_at->format('d/m/Y H:i') : 'N/A' }}
        </div>
    </div>
    
</div>

@push('css')
    <style>
        .table-borderless td, .table-borderless th {
            border: 0;
            padding: 0.5rem;
        }
        .card-header {
            padding: 0.75rem 1.25rem;
        }
        .table-sm td, .table-sm th {
            padding: 0.5rem;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,.05);
        }
        .badge-info {
            background-color: #17a2b8;
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