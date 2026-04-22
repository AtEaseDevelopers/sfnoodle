<div class="row">
    <!-- Left Column -->
    <div class="col-md-6">
        <!-- Code Field -->
        <div class="form-group">
            {!! Form::label('code', __('products.code')) !!}:
            <p>{{ $product->code }}</p>
        </div>

        <!-- Name Field -->
        <div class="form-group">
            {!! Form::label('name', __('products.name')) !!}:
            <p>{{ $product->name }}</p>
        </div>

        <!-- Price Field -->
        <div class="form-group">
            {!! Form::label('price', __('products.price')) !!}:
            <p>{{ number_format($product->price, 2) }}</p>
        </div>

        <!-- Category Field -->
        <div class="form-group">
            {!! Form::label('category', __('Category')) !!}:
            <p>{{ $product->category ?: 'N/A' }}</p>
        </div>

        <!-- UOM Field -->
        <div class="form-group">
            {!! Form::label('uom', __('UOM')) !!}:
            <p>{{ $product->uom ?? 'N/A' }}</p>
        </div>

        <!-- Status Field -->
        <div class="form-group">
            {!! Form::label('status', __('products.status')) !!}:
            <p>{{ $product->status == 1 ? __('products.active') : __('products.unactive') }}</p>
        </div>
    </div>

    <!-- Right Column - Product Image -->
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('image', __('Product Image')) !!}:
            <div class="text-center mt-2">
                @if($product->image_path && file_exists(public_path($product->image_path)))
                    <img src="{{ asset($product->image_path) }}" 
                         alt="{{ $product->name }}" 
                         style="max-width: 100%; max-height: 300px; object-fit: contain; border: 1px solid #ddd; padding: 10px;">
                @else
                    <div class="text-center p-5 border rounded bg-light">
                        <i class="fas fa-image fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No image available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Tiered Pricing Section -->
    @if($product->tiered_pricing && count($product->tiered_pricing) > 0)
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Volume Pricing</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-light">
                                    <th>Minimum Quantity</th>
                                    <th>Price per Unit</th>
                                    <th>Total for Minimum Quantity</th>
                                    <th>Savings vs Regular Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->tiered_pricing as $tier)
                                    @php
                                        $regularTotal = $product->price * $tier['quantity'];
                                        $tierTotal = $tier['price'] * $tier['quantity'];
                                        $savings = $regularTotal - $tierTotal;
                                        $savingsPercent = $regularTotal > 0 ? ($savings / $regularTotal) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td><strong>≥ {{ number_format($tier['quantity']) }} units</strong></td>
                                        <td>{{ number_format($tier['price'], 2) }}</td>
                                        <td>{{ number_format($tierTotal, 2) }}</td>
                                        <td class="text-success">
                                            Save {{ number_format($savings, 2) }} ({{ number_format($savingsPercent, 1) }}%)
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- Blocked Drivers Section -->
    @if($product->blocked_drivers && count($product->blocked_drivers) > 0)
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user-slash"></i> Blocked Drivers</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>This product is hidden from the following drivers:</strong>
                        <ul class="mt-2 mb-0">
                            @php
                                $blockedDrivers = App\Models\Driver::whereIn('id', $product->blocked_drivers)->get();
                            @endphp
                            @foreach($blockedDrivers as $driver)
                                <li>{{ $driver->name }} </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user-slash"></i> Blocked Drivers</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No drivers are blocked from seeing this product. Visible to all drivers.
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('.card .card-header a')[0].click();
            }
        });
        $(document).ready(function () {
            HideLoad();
        });
    </script>
@endpush