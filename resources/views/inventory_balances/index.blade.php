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
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1200px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ __('inventory_balances.stock_in') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => 'inventoryBalances.stockin', 'enctype' => 'multipart/form-data', 'id' => 'stockinForm']) !!}
                    
                    <!-- Driver Multi-Select Dropdown -->
                    <div class="form-group">
                        <label for="driver_ids" class="col-form-label font-weight-bold">{{ __('Agent') }} <span class="text-danger">*</span>:</label>
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

                    <!-- Items Table - Multiple Products -->
                    <div class="form-group mt-4">
                        <label class="col-form-label font-weight-bold">{{ __('Items') }} <span class="text-danger">*</span>:</label>
                        <div class="items-scroll-wrapper" style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; background: #fff;">
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="table table-bordered mb-0" id="stockinItemsTable" style="min-width: 700px;">
                                    <thead class="bg-light position-sticky top-0" style="position: sticky; top: 0; z-index: 10; background: #f8f9fa;">
                                        <tr>
                                            <th width="5%" class="text-center">#</th>
                                            <th width="55%">Product <span class="text-danger">*</span></th>
                                            <th width="25%">Quantity <span class="text-danger">*</span></th>
                                            <th width="15%" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="stockinItemsBody">
                                        <!-- Items will be added here dynamically -->
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="4" class="text-right py-2">
                                                <button type="button" class="btn btn-success btn-sm" id="addStockinItemBtn">
                                                    <i class="fa fa-plus mr-1"></i> Add Item
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="text-danger small mt-1" id="stockinItemsError"></div>
                    </div>
                    
                    <div class="modal-footer border-0 px-0 pb-0 pt-4">
                        <button type="button" class="btn btn-secondary rounded-0 px-4" data-dismiss="modal">{{ __('inventory_balances.cancel') }}</button>
                        <button type="submit" name="button" class="btn btn-primary rounded-0 px-4" id="stockinSubmitBtn">{{ __('inventory_balances.update') }}</button>
                    </div>
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
    // ========== STOCK IN MODAL WITH MULTIPLE PRODUCTS ==========
    var stockinItemCounter = 0;
    var selectedDrivers = [];
    var blockedProductIds = [];

    // Update selected drivers list and dropdown button
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
            
            // Update hidden input value
            var driverIds = selectedDrivers.map(function(d) { return d.id; });
            $('#selectedDriverStockIn').val(driverIds.join(','));
            
            // Update dropdown button text
            if (selectedDrivers.length === 1) {
                $('#dropdownDriverStockIn').html('<i class="fa fa-user mr-2"></i>' + selectedDrivers[0].name);
            } else {
                $('#dropdownDriverStockIn').html('<i class="fa fa-users mr-2"></i>' + selectedDrivers.length + ' agents selected');
            }
        } else {
            container.hide();
            $('#selectedDriverStockIn').val('');
            $('#dropdownDriverStockIn').html('<i class="fa fa-user mr-2"></i> Select Agents');
        }
        
        // After drivers change, re-filter products
        if (selectedDrivers.length > 0) {
            filterBlockedProducts();
        } else {
            restoreAllProducts();
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

    // Function to get blocked products for selected drivers
    function filterBlockedProducts() {
        if (selectedDrivers.length === 0) {
            restoreAllProducts();
            return;
        }
        
        var driverIds = selectedDrivers.map(function(d) { return d.id; });
        
        console.log('Filtering blocked products for drivers:', driverIds);
        
        $.ajax({
            url: '/inventoryBalances/get-blocked-products',
            type: 'POST',
            data: {
                driver_ids: driverIds,
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(response) {
                console.log('Blocked products response:', response);
                if (response.success) {
                    blockedProductIds = response.blocked_product_ids;
                    console.log('Blocked product IDs:', blockedProductIds);
                    
                    restoreAllProducts();
                    
                    // Remove blocked products from dropdowns
                    $('.stockin-product-list .stockin-product-select-item').each(function() {
                        var productId = $(this).data('value');
                        if (blockedProductIds.includes(productId)) {
                            console.log('Removing blocked product:', productId);
                            $(this).remove();
                        }
                    });
                    
                    // Show message if no products available
                    $('.stockin-product-list').each(function() {
                        var $list = $(this);
                        var hasProducts = $list.find('.stockin-product-select-item').length > 0;
                        $list.find('.no-products-message').remove();
                        
                        if (!hasProducts) {
                            $list.append('<div class="alert alert-warning no-products-message mt-2">No products available for selected drivers</div>');
                        }
                    });
                }
            },
            error: function(xhr) {
                console.log('Error fetching blocked products:', xhr);
            }
        });
    }

    // Function to restore all products
    function restoreAllProducts() {
        console.log('Restoring all products');
        
        // For each product list, restore all products
        $('.stockin-product-list').each(function() {
            var $list = $(this);
            var currentIndex = $list.data('index');
            
            // Clear current list
            $list.empty();
            
            // Add back all products from the original productItems
            @foreach($productItems as $productId => $productName)
                @php
                    $product = \App\Models\Product::find($productId);
                    $productCode = $product ? $product->code : '';
                @endphp
                $list.append(`
                    <a href="#" class="list-group-item list-group-item-action stockin-product-select-item border-0 py-2 rounded mb-1" 
                        data-index="${currentIndex}" 
                        data-value="{{ $productId }}" 
                        data-name="{{ $productName }}" 
                        data-code="{{ $productCode }}">
                        <i class="fa fa-cube mr-2 text-secondary"></i>{{ $productName }} ({{ $productCode }})
                    </a>
                `);
            @endforeach
            
            // Remove any "no products" message
            $list.find('.no-products-message').remove();
        });
    }

    // Initialize stockin items table
    function initializeStockinItemsTable() {
        $('#stockinItemsBody').empty();
        stockinItemCounter = 0;
        addStockinItemRow();
    }

    // Add item row to stockin modal
    function addStockinItemRow(productId = '', productName = '', productCode = '', quantity = '') {
        var displayName = productName ? `${productName} (${productCode})` : 'Select Product';
        var row = `
            <tr class="stockin-item-row" data-index="${stockinItemCounter}">
                <td class="align-middle text-center font-weight-bold">${stockinItemCounter + 1}</td>
                <td style="min-width: 300px;">
                    <div class="dropdown w-100">
                        <button class="btn btn-outline-secondary btn-block dropdown-toggle text-left product-dropdown py-2" type="button" id="stockinProductDropdown${stockinItemCounter}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 8px; white-space: normal; word-wrap: break-word;">
                            <i class="fa fa-cube mr-2"></i>${displayName}
                        </button>
                        <div class="dropdown-menu p-3 shadow" aria-labelledby="stockinProductDropdown${stockinItemCounter}" style="width: 100%; min-width: 400px; max-height: 400px; overflow-y: auto;">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                                </div>
                                <input type="text" class="form-control stockin-product-search" placeholder="Search Products..." data-index="${stockinItemCounter}">
                            </div>
                            <div class="stockin-product-list" data-index="${stockinItemCounter}" style="max-height: 220px; overflow-y: auto;">
                                @foreach($productItems as $productId => $productName)
                                    @php
                                        $product = \App\Models\Product::find($productId);
                                        $productCode = $product ? $product->code : '';
                                    @endphp
                                    <a href="#" class="list-group-item list-group-item-action stockin-product-select-item border-0 py-2 rounded mb-1" 
                                        data-index="${stockinItemCounter}" 
                                        data-value="{{ $productId }}" 
                                        data-name="{{ $productName }}" 
                                        data-code="{{ $productCode }}">
                                        <i class="fa fa-cube mr-2 text-secondary"></i>{{ $productName }} ({{ $productCode }})
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" class="stockin-product-id-input" name="items[${stockinItemCounter}][product_id]" value="${productId}">
                    <div class="text-danger stockin-product-error small mt-1"></div>
                </div>
                <td style="min-width: 140px;">
                    <input type="number" min="0.01" step="0.01" class="form-control stockin-quantity-input" name="items[${stockinItemCounter}][quantity]" placeholder="Enter quantity" value="${quantity}" style="border-radius: 8px;">
                    <div class="text-danger stockin-quantity-error small mt-1"></div>
                </div>
                <td class="align-middle text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill remove-stockin-item-btn" ${stockinItemCounter === 0 ? 'disabled' : ''}>
                        <i class="fa fa-trash-o mr-1"></i> Remove
                    </button>
                </div>
            </tr>
        `;
        
        $('#stockinItemsBody').append(row);
        stockinItemCounter++;
        
        // Enable remove buttons if more than one row
        if ($('#stockinItemsBody tr').length > 1) {
            $('#stockinItemsBody tr:first .remove-stockin-item-btn').prop('disabled', false);
        }
    }

    // Product search in stockin modal
    $(document).on('keyup', '.stockin-product-search', function() {
        var searchTerm = $(this).val().toLowerCase();
        var dropdownMenu = $(this).closest('.dropdown-menu');
        var productList = dropdownMenu.find('.stockin-product-list');
        
        productList.find('.stockin-product-select-item').each(function() {
            var productText = $(this).text().toLowerCase();
            if (productText.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Product selection in stockin modal
    $(document).on('click', '.stockin-product-select-item', function(e) {
        e.preventDefault();
        var index = $(this).data('index');
        var productId = $(this).data('value');
        var productName = $(this).data('name');
        var productCode = $(this).data('code');
        
        $('#stockinProductDropdown' + index).html(`<i class="fa fa-cube mr-2"></i>${productName} (${productCode})`).attr('title', `${productName} (${productCode})`);
        $(this).closest('tr').find('.stockin-product-id-input').val(productId);
        $(this).closest('tr').find('.stockin-product-error').text('');
        $(this).siblings().removeClass('active');
        $(this).addClass('active bg-light');
        
        $(this).closest('.dropdown').removeClass('show');
        $(this).closest('.dropdown-menu').removeClass('show');
    });

    // Add item button
    $('#addStockinItemBtn').on('click', function() {
        addStockinItemRow();
    });

    // Remove item button
    $(document).on('click', '.remove-stockin-item-btn', function() {
        if ($('#stockinItemsBody tr').length > 1) {
            var row = $(this).closest('tr');
            row.remove();
            
            $('#stockinItemsBody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
                $(this).attr('data-index', index);
                
                var dropdownBtn = $(this).find('.product-dropdown');
                var dropdownId = 'stockinProductDropdown' + index;
                dropdownBtn.attr('id', dropdownId).attr('aria-labelledby', dropdownId);
                
                $(this).find('.stockin-product-search, .stockin-product-list').attr('data-index', index);
                $(this).find('.stockin-product-select-item').attr('data-index', index);
                
                $(this).find('.stockin-product-id-input, .stockin-quantity-input').each(function() {
                    var name = $(this).attr('name');
                    if (name && name.includes('items[')) {
                        var newName = name.replace(/items\[\d+\]/, 'items[' + index + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
            
            stockinItemCounter = $('#stockinItemsBody tr').length;
            
            if ($('#stockinItemsBody tr').length === 1) {
                $('#stockinItemsBody tr:first .remove-stockin-item-btn').prop('disabled', true);
            }
        }
    });

    // Driver checkbox change handler for Stock In - Using delegated event
    $(document).on('change', '#stockin .driver-checkbox', function() {
        var driverId = $(this).val();
        var driverName = $(this).data('name');
        
        console.log('Driver checkbox changed:', driverId, driverName, $(this).is(':checked'));
        
        if ($(this).is(':checked')) {
            if (!selectedDrivers.some(function(d) { return d.id == driverId; })) {
                selectedDrivers.push({
                    id: driverId,
                    name: driverName
                });
                console.log('Driver added:', selectedDrivers);
            }
        } else {
            selectedDrivers = selectedDrivers.filter(function(d) { return d.id != driverId; });
            console.log('Driver removed:', selectedDrivers);
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

    // Reset stock in modal when closed
    $('#stockin').on('hidden.bs.modal', function() {
        console.log('Modal closed, resetting...');
        selectedDrivers = [];
        blockedProductIds = [];
        $('#driverListStockIn .driver-checkbox').prop('checked', false);
        updateSelectedDriversList();
        initializeStockinItemsTable();
        $('#driverSearchStockIn').val('');
        $('#stockinSubmitBtn').prop('disabled', false).html('{{ __("inventory_balances.update") }}');
    });

    // Validate form before submission for Stock In
    $('#stockinForm').on('submit', function(e) {
        var hasDrivers = selectedDrivers.length > 0;
        
        if (!hasDrivers) {
            e.preventDefault();
            if (typeof noti === 'function') {
                noti('e', 'Validation Error', 'Please select at least one agent');
            } else {
                alert('Please select at least one agent');
            }
            return false;
        }
        
        var hasErrors = false;
        var items = [];
        var productIds = new Set();
        
        $('#stockinItemsBody tr').each(function() {
            var productId = $(this).find('.stockin-product-id-input').val();
            var quantity = $(this).find('.stockin-quantity-input').val();
            var productError = $(this).find('.stockin-product-error');
            var quantityError = $(this).find('.stockin-quantity-error');
            
            productError.text('');
            quantityError.text('');
            
            if (!productId) {
                productError.text('⚠️ Please select a product');
                hasErrors = true;
            } else if (productIds.has(productId)) {
                productError.text('⚠️ Duplicate product selected');
                hasErrors = true;
            } else {
                productIds.add(productId);
            }
            
            if (!quantity || quantity <= 0) {
                quantityError.text('⚠️ Please enter a valid quantity (minimum 0.01)');
                hasErrors = true;
            }
            
            if (productId && quantity && quantity > 0) {
                items.push({
                    product_id: parseInt(productId),
                    quantity: parseFloat(quantity)
                });
            }
        });
        
        if (items.length === 0) {
            $('#stockinItemsError').text('⚠️ Please add at least one valid item');
            hasErrors = true;
        } else {
            $('#stockinItemsError').text('');
        }
        
        if (hasErrors) {
            e.preventDefault();
            return false;
        }
        
        $('#stockinSubmitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        
        return true;
    });

    // Initialize stockin items table on modal open
    $('#stockin').on('show.bs.modal', function() {
        console.log('Modal opened, initializing...');
        initializeStockinItemsTable();
    });
</script>
@endpush