@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">{{ __('inventory_balances.inventory_balances') }}</li>
    </ol>
    <div class="container-fluid">
        <div class="animated fadeIn">
            @include('flash::message')
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-align-justify"></i>
                            {{ __('inventory_balances.inventory_balances') }}
                            <button class="border-0 bg-transparent pull-right text-success pr-2" data-toggle="modal" data-target="#stockin"><i class="fa fa-cart-plus fa-lg"></i></button>
                        </div>
                        <div class="card-body">
                            @include('inventory_balances.table')
                            <div class="pull-right mr-3">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Modal -->
    <div class="modal fade" id="productDetailsModal" tabindex="-1" role="dialog" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productDetailsModalLabel">Product Details - <span id="modalDriverName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="productDetailsTable">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Product Code</th>
                                    <th>Product Name</th>
                                    <th class="text-center">Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="productDetailsBody">
                                <!-- Product details will be loaded here -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th class="text-center" id="modalTotalQuantity">0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock In Modal -->
    <div id="stockin" class="modal fade">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ __('inventory_balances.stock_in') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    {!! Form::open(['route' => 'inventoryBalances.stockin', 'enctype' => 'multipart/form-data', 'id' => 'stockinForm']) !!}
                    
                    <!-- Driver Multi-Select Dropdown -->
                    <div class="form-group">
                        <label for="driver_ids" class="col-form-label">{{ __('Agent') }} <span class="text-danger">*</span>:</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-block dropdown-toggle" type="button" id="dropdownDriverStockIn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ __('Select Agents') }}
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="dropdownDriverStockIn" style="width: 100%; max-height: 400px; overflow-y: auto;">
                                <input type="text" class="form-control mb-3" id="driverSearchStockIn" placeholder="Search Agents...">
                                <div id="driverListStockIn" class="list-group">
                                    @foreach($driverItems as $driverId => $driverName)
                                        <label class="list-group-item driver-item">
                                            <input type="checkbox" class="driver-checkbox" value="{{ $driverId }}" data-name="{{ $driverName }}">
                                            {{ $driverName }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div id="selectedDriversContainer" class="mt-2" style="display: none;">
                            <small class="text-muted">Selected agents:</small>
                            <div id="selectedDriversList" class="mt-1"></div>
                        </div>
                        <input type="hidden" name="driver_ids" id="selectedDriverStockIn">
                        <small class="form-text text-muted">You can select multiple agents</small>
                    </div>

                    <!-- Product Single-Select Dropdown -->
                    <div class="form-group">
                        <label for="product_id" class="col-form-label">{{ __('inventory_balances.product') }} <span class="text-danger">*</span>:</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-block dropdown-toggle" type="button" id="dropdownProductStockIn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ __('Select Product') }}
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="dropdownProductStockIn" style="width: 100%; max-height: 300px; overflow-y: auto;">
                                <input type="text" class="form-control mb-3" id="productSearchStockIn" placeholder="Search Products...">
                                <div id="productListStockIn" class="list-group">
                                    @foreach($productItems as $productId => $productName)
                                        <a href="#" class="list-group-item list-group-item-action product-item" data-value="{{ $productId }}">
                                            {{ $productName }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="product_id" id="selectedProductStockIn">
                    </div>

                    <div class="form-group">
                        <label for="quantity" class="col-form-label">{{ __('inventory_balances.transfer_quantity') }} <span class="text-danger">*</span>:</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">+</span>
                            </div>
                            <input type="number" min="0.01" step="0.01" class="form-control" placeholder="Transfer Quantity" name="quantity" id="stockin_quantity" required>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-secondary rounded-0 mt-2" data-dismiss="modal">{{ __('inventory_balances.cancel') }}</button>
                    <button type="submit" name="button" class="btn btn-primary rounded-0 mt-2" id="stockinSubmitBtn">{{ __('inventory_balances.update') }}</button>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Out Modal -->
    <div id="stockout" class="modal fade">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ __('inventory_balances.stock_out') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    {!! Form::open(['route' => 'inventoryBalances.stockout', 'enctype' => 'multipart/form-data']) !!}
                    
                    <!-- Driver Single-Select Dropdown -->
                    <div class="form-group">
                        <label for="driver_id" class="col-form-label">{{ __('Agent') }}:</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-block dropdown-toggle" type="button" id="dropdownDriverStockOut" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ __('Select Agent') }}
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="dropdownDriverStockOut" style="width: 100%; max-height: 300px; overflow-y: auto;">
                                <input type="text" class="form-control mb-3" id="driverSearchStockOut" placeholder="Search Agents...">
                                <div id="driverListStockOut">
                                    @foreach($driverItems as $driverId => $driverName)
                                        <a href="#" class="list-group-item list-group-item-action driver-item" data-value="{{ $driverId }}">
                                            {{ $driverName }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="driver_id" id="selectedDriverStockOut">
                    </div>

                    <!-- Product Single-Select Dropdown -->
                    <div class="form-group">
                        <label for="product_id" class="col-form-label">{{ __('inventory_balances.product') }}:</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-block dropdown-toggle" type="button" id="dropdownProductStockOut" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ __('Select Product') }}
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="dropdownProductStockOut" style="width: 100%; max-height: 300px; overflow-y: auto;">
                                <input type="text" class="form-control mb-3" id="productSearchStockOut" placeholder="Search Products...">
                                <div id="productListStockOut" class="list-group">
                                    @foreach($productItems as $productId => $productName)
                                        <a href="#" class="list-group-item list-group-item-action product-item" data-value="{{ $productId }}">
                                            {{ $productName }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="product_id" id="selectedProductStockOut">
                    </div>

                    <div class="form-group">
                        <label for="quantity" class="col-form-label">{{ __('inventory_balances.transfer_quantity') }}:</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">-</span>
                            </div>
                            <input type="number" min="0" class="form-control" placeholder="Transfer Quantity" name="quantity" id="quantity">
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary rounded-0 mt-2" data-dismiss="modal">{{ __('inventory_balances.cancel') }}</button>
                    <button type="submit" name="button" class="btn btn-primary rounded-0 mt-2">{{ __('inventory_balances.update') }}</button>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        .dropdown-menu {
            border-radius: 0.25rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .dropdown-menu .form-check {
            padding: 0.25rem 0;
        }
        .dropdown-toggle::after {
            margin-left: 10px;
        }
        .dropdown-menu::-webkit-scrollbar {
            width: 8px;
        }
        .dropdown-menu::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .dropdown-menu::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .btn-outline-primary {
            border-color: #007bff;
            color: #007bff;
        }
        .btn-outline-primary:hover {
            background-color: #007bff;
            color: #fff;
        }
        .driver-item:hover, 
        .driver-item.active,
        .product-item:hover, 
        .product-item.active {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        .list-group-item {
            border: 1px solid rgba(0,0,0,.125);
            margin-bottom: -1px;
        }
        
        /* Modal table styling */
        #productDetailsTable th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        #productDetailsTable tbody tr:hover {
            background-color: rgba(0,0,0,.075);
        }
        
        /* Multi-select driver styling */
        .driver-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .driver-item:hover {
            background-color: #f8f9fa;
        }

        .driver-item .driver-checkbox {
            margin-right: 10px;
            cursor: pointer;
        }

        .driver-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        #selectedDriversContainer {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
        }

        #selectedDriversList .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        #selectedDriversList .badge .close {
            font-size: 14px;
            line-height: 1;
            opacity: 0.8;
        }

        #selectedDriversList .badge .close:hover {
            opacity: 1;
        }
    </style>

    <script>
    // Stock In Modal - Driver multi-selection
    var selectedDrivers = [];

    function updateSelectedDriversList() {
        var container = $('#selectedDriversContainer');
        var listContainer = $('#selectedDriversList');
        
        if (selectedDrivers.length > 0) {
            container.show();
            listContainer.empty();
            
            selectedDrivers.forEach(function(driver, index) {
                var badge = $('<span class="badge badge-primary mr-2 mb-1" style="padding: 5px 10px; font-size: 12px;">')
                    .text(driver.name)
                    .append('<button type="button" class="close ml-2" style="font-size: 12px; color: white;" data-driver-id="' + driver.id + '" aria-label="Remove">&times;</button>');
                
                badge.find('button').on('click', function(e) {
                    e.stopPropagation();
                    removeDriver(driver.id);
                });
                
                listContainer.append(badge);
            });
            
            // Update hidden input value (store as comma-separated string)
            var driverIds = selectedDrivers.map(function(d) { return d.id; });
            $('#selectedDriverStockIn').val(driverIds.join(','));
            
            // Update dropdown button text
            if (selectedDrivers.length === 1) {
                $('#dropdownDriverStockIn').text(selectedDrivers[0].name);
            } else {
                $('#dropdownDriverStockIn').text(selectedDrivers.length + ' agents selected');
            }
        } else {
            container.hide();
            $('#selectedDriverStockIn').val('');
            $('#dropdownDriverStockIn').text('Select Agents');
        }
    }

    function removeDriver(driverId) {
        selectedDrivers = selectedDrivers.filter(function(d) { return d.id != driverId; });
        
        // Uncheck the corresponding checkbox
        $('#driverListStockIn .driver-checkbox').each(function() {
            if ($(this).val() == driverId) {
                $(this).prop('checked', false);
            }
        });
        
        updateSelectedDriversList();
    }

    $(document).ready(function () {
        // ========== STOCK IN MODAL HANDLERS ==========
        // Driver checkbox change handler for Stock In
        $('#stockin .driver-checkbox').on('change', function() {
            var driverId = $(this).val();
            var driverName = $(this).data('name');
            
            if ($(this).is(':checked')) {
                // Add driver if not already selected
                if (!selectedDrivers.some(function(d) { return d.id == driverId; })) {
                    selectedDrivers.push({
                        id: driverId,
                        name: driverName
                    });
                }
            } else {
                // Remove driver
                selectedDrivers = selectedDrivers.filter(function(d) { return d.id != driverId; });
            }
            
            updateSelectedDriversList();
        });

        // Driver search functionality for Stock In modal
        $('#driverSearchStockIn').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            var driverLabels = $('#driverListStockIn .driver-item');
            
            driverLabels.each(function() {
                var driverText = $(this).text().toLowerCase();
                if (driverText.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Product selection for Stock In modal
        $('#stockin .product-item').on('click', function(e) {
            e.preventDefault();
            var productName = $(this).text();
            var productId = $(this).data('value');
            var dropdown = $(this).closest('.dropdown-menu');
            
            // Update UI - remove active class from all siblings and add to current
            $(this).closest('.list-group').find('.product-item').removeClass('active');
            $(this).addClass('active');
            dropdown.prev('.dropdown-toggle').text(productName);
            
            // Set hidden input value
            $('#selectedProductStockIn').val(productId);
        });

        // Product search functionality for Stock In modal
        $('#productSearchStockIn').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            var productList = $('#productListStockIn .product-item');
            
            productList.each(function() {
                var productText = $(this).text().toLowerCase();
                if (productText.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Reset stock in modal when closed
        $('#stockin').on('hidden.bs.modal', function() {
            // Reset driver selections
            selectedDrivers = [];
            $('#driverListStockIn .driver-checkbox').prop('checked', false);
            updateSelectedDriversList();
            
            // Reset product selection
            $('#productListStockIn .product-item').removeClass('active');
            $('#dropdownProductStockIn').text('Select Product');
            $('#selectedProductStockIn').val('');
            
            // Reset quantity
            $('#stockin_quantity').val('');
            
            // Reset search inputs
            $('#driverSearchStockIn').val('');
            $('#productSearchStockIn').val('');
            
            // Show all driver items again
            $('#driverListStockIn .driver-item').show();
            $('#productListStockIn .product-item').show();
            
            // Reset submit button
            $('#stockinSubmitBtn').prop('disabled', false).html('{{ __("inventory_balances.update") }}');
        });

        // Validate form before submission for Stock In
        $('#stockinForm').on('submit', function(e) {
            var hasDrivers = selectedDrivers.length > 0;
            var hasProduct = $('#selectedProductStockIn').val();
            var quantity = $('#stockin_quantity').val();
            
            if (!hasDrivers) {
                e.preventDefault();
                if (typeof noti === 'function') {
                    noti('e', 'Validation Error', 'Please select at least one agent');
                } else {
                    alert('Please select at least one agent');
                }
                return false;
            }
            
            if (!hasProduct) {
                e.preventDefault();
                if (typeof noti === 'function') {
                    noti('e', 'Validation Error', 'Please select a product');
                } else {
                    alert('Please select a product');
                }
                return false;
            }
            
            if (!quantity || quantity <= 0) {
                e.preventDefault();
                if (typeof noti === 'function') {
                    noti('e', 'Validation Error', 'Please enter a valid quantity');
                } else {
                    alert('Please enter a valid quantity');
                }
                return false;
            }
            
            // Show loading state
            $('#stockinSubmitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            
            return true;
        });

        // ========== STOCK OUT MODAL HANDLERS ==========
        // Driver selection for Stock Out modal
        $('#stockout .driver-item').on('click', function(e) {
            e.preventDefault();
            var driverName = $(this).text();
            var driverId = $(this).data('value');
            var dropdown = $(this).closest('.dropdown-menu');
            
            // Update UI - remove active class from all siblings and add to current
            $(this).closest('.list-group').find('.driver-item').removeClass('active');
            $(this).addClass('active');
            dropdown.prev('.dropdown-toggle').text(driverName);
            
            // Set hidden input value
            $('#selectedDriverStockOut').val(driverId);
            
            // Trigger stock calculation if product is selected
            if ($('#selectedProductStockOut').val()) {
                getstock();
            }
        });

        // Driver search functionality for Stock Out modal
        $('#driverSearchStockOut').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            var driverList = $('#driverListStockOut .driver-item');
            
            driverList.each(function() {
                var driverText = $(this).text().toLowerCase();
                if (driverText.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Product selection for Stock Out modal
        $('#stockout .product-item').on('click', function(e) {
            e.preventDefault();
            var productName = $(this).text();
            var productId = $(this).data('value');
            var dropdown = $(this).closest('.dropdown-menu');
            
            // Update UI - remove active class from all siblings and add to current
            $(this).closest('.list-group').find('.product-item').removeClass('active');
            $(this).addClass('active');
            dropdown.prev('.dropdown-toggle').text(productName);
            
            // Set hidden input value
            $('#selectedProductStockOut').val(productId);
            
            // Trigger stock calculation if driver is selected
            if ($('#selectedDriverStockOut').val()) {
                getstock();
            }
        });

        // Product search functionality for Stock Out modal
        $('#productSearchStockOut').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            var productList = $('#productListStockOut .product-item');
            
            productList.each(function() {
                var productText = $(this).text().toLowerCase();
                if (productText.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Reset stock out modal when closed
        $('#stockout').on('hidden.bs.modal', function() {
            // Reset driver selection
            $('#driverListStockOut .driver-item').removeClass('active');
            $('#dropdownDriverStockOut').text('Select Agent');
            $('#selectedDriverStockOut').val('');
            
            // Reset product selection
            $('#productListStockOut .product-item').removeClass('active');
            $('#dropdownProductStockOut').text('Select Product');
            $('#selectedProductStockOut').val('');
            
            // Reset quantity
            $('#quantity').val('').prop('disabled', false);
            
            // Reset search inputs
            $('#driverSearchStockOut').val('');
            $('#productSearchStockOut').val('');
            
            // Show all items again
            $('#driverListStockOut .driver-item').show();
            $('#productListStockOut .product-item').show();
        });

        // Initialize dropdown buttons with default text
        $('.dropdown-toggle').each(function() {
            if (!$(this).text().trim()) {
                if ($(this).closest('.dropdown-menu').find('.driver-item').length) {
                    $(this).text('Select Agent');
                } else if ($(this).closest('.dropdown-menu').find('.product-item').length) {
                    $(this).text('Select Product');
                }
            }
        });

        // Handle view button click
        $(document).on('click', '.view-products', function() {
            var driverId = $(this).data('driver-id');
            var driverName = $(this).data('driver-name');
            
            // Show loading in modal
            $('#modalDriverName').text(driverName);
            $('#productDetailsBody').html('<tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading product details...</td></tr>');
            $('#modalTotalQuantity').text('0');
            
            // Load product details via AJAX
            $.ajax({
                url: '{{ route("inventoryBalances.getProductsByDriver") }}',
                type: 'GET',
                data: {
                    driver_id: driverId
                },
                success: function(response) {
                    if (response.success) {
                        var html = '';
                        var totalQuantity = 0;
                        var index = 1;
                        
                        // Sort products alphabetically by name
                        response.products.sort(function(a, b) {
                            return a.product_name.localeCompare(b.product_name);
                        });
                        
                        response.products.forEach(function(product) {
                            if (product.quantity > 0) {
                                html += '<tr>';
                                html += '<td class="text-center">' + index + '</td>';
                                html += '<td>' + (product.product_code || '-') + '</td>';
                                html += '<td>' + product.product_name + '</td>';
                                html += '<td class="text-center"><span class="badge badge-primary">' + product.quantity + '</span></td>';
                                html += '</tr>';
                                totalQuantity += product.quantity;
                                index++;
                            }
                        });
                        
                        if (html === '') {
                            html = '<tr><td colspan="4" class="text-center text-muted">No products found</td></tr>';
                        }
                        
                        $('#productDetailsBody').html(html);
                        $('#modalTotalQuantity').text(totalQuantity);
                    } else {
                        $('#productDetailsBody').html('<tr><td colspan="4" class="text-center text-danger">' + response.message + '</td></tr>');
                    }
                },
                error: function(xhr) {
                    $('#productDetailsBody').html('<tr><td colspan="4" class="text-center text-danger">Error loading product details</td></tr>');
                }
            });
        });
    });

    $(document).keyup(function(e) {
        if(e.altKey && e.keyCode == 78){
            $('.card .card-header a')[0].click();
        }
    });

    // Stock out specific functionality
    function getstock() {
        // Get selected driver ID
        var driverId = $('#selectedDriverStockOut').val();
        
        // Get selected product ID
        var productId = $('#selectedProductStockOut').val();

        if (driverId && productId) {
            ShowLoad();
            $.ajax({
                url: '{{ ENV("APP_URL") }}' + '/inventoryBalances/getstock/' + driverId + '/' + productId,
                type: 'GET',
                success: function(data) {
                    if (data.status) {
                        $('#stockout').find('#quantity').prop('disabled', false);
                        $('#stockout').find('#quantity').val(data.quantity);
                    } else {
                        $('#stockout').find('#quantity').prop('disabled', true);
                        $('#stockout').find('#quantity').val(data.quantity);
                        if (typeof noti === 'function') {
                            noti('e', 'Warning', data.message);
                        }
                    }
                    HideLoad();
                },
                error: function(error) {
                    if (typeof noti === 'function') {
                        noti('e', 'Please contact your administrator', error.responseJSON?.message || 'Unknown error');
                    }
                    HideLoad();
                }
            });
        }
    }
    </script>
@endpush