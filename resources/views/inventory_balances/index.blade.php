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
                    {!! Form::open(['route' => 'inventoryBalances.stockin', 'enctype' => 'multipart/form-data']) !!}
                    
                    <!-- Lorry Multi-Select Dropdown -->
                    <div class="form-group">
                        <label for="lorry_id" class="col-form-label">{{ __('inventory_balances.lorry') }}:</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-block dropdown-toggle" type="button" id="dropdownLorryStockIn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ __('inventory_balances.select_lorry') }}
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="dropdownLorryStockIn" style="width: 100%; max-height: 300px; overflow-y: auto;">
                                <input type="text" class="form-control mb-3" id="lorrySearchStockIn" placeholder="Search Lorries...">
                                <div id="lorryListStockIn">
                                    @foreach($lorryItems as $lorryId => $lorryName)
                                        <div class="form-check ml-3">
                                            <input class="form-check-input lorry-checkbox" type="checkbox" name="lorry_id[]" value="{{ $lorryId }}" id="lorry_stockin_{{ $lorryId }}">
                                            <label class="form-check-label" for="lorry_stockin_{{ $lorryId }}">
                                                {{ $lorryName }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Single-Select Dropdown -->
                    <div class="form-group">
                        <label for="product_id" class="col-form-label">{{ __('inventory_balances.product') }}:</label>
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
                        <label for="quantity" class="col-form-label">{{ __('inventory_balances.transfer_quantity') }}:</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">+</span>
                            </div>
                            <input type="number" min="0" class="form-control" placeholder="Transfer Quantity" name="quantity">
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary rounded-0 mt-2" data-dismiss="modal">{{ __('inventory_balances.cancel') }}</button>
                    <button type="submit" name="button" class="btn btn-primary rounded-0 mt-2">{{ __('inventory_balances.update') }}</button>
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
                    
                    <!-- Lorry Multi-Select Dropdown -->
                    <div class="form-group">
                        <label for="lorry_id" class="col-form-label">{{ __('inventory_balances.lorry') }}:</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-block dropdown-toggle" type="button" id="dropdownLorryStockOut" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ __('inventory_balances.select_lorry') }}
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="dropdownLorryStockOut" style="width: 100%; max-height: 300px; overflow-y: auto;">
                                <input type="text" class="form-control mb-3" id="lorrySearchStockOut" placeholder="Search Lorries...">
                                <div id="lorryListStockOut">
                                    @foreach($lorryItems as $lorryId => $lorryName)
                                        <div class="form-check ml-3">
                                            <input class="form-check-input lorry-checkbox" type="checkbox" name="lorry_id[]" value="{{ $lorryId }}" id="lorry_stockout_{{ $lorryId }}">
                                            <label class="form-check-label" for="lorry_stockout_{{ $lorryId }}">
                                                {{ $lorryName }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
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
    </style>

    <script>
        $(document).ready(function () {
            // Lorry checkbox functionality for both modals
            $('.lorry-checkbox').change(function () {
                var dropdownButton = $(this).closest('.dropdown-menu').prev('.dropdown-toggle');
                var selected = [];
                
                $(this).closest('.dropdown-menu').find('.lorry-checkbox:checked').each(function () {
                    selected.push($(this).next('label').text());
                });

                if (selected.length > 0) {
                    dropdownButton.text(selected.join(', '));
                } else {
                    dropdownButton.text('Select Lorries');
                }
            });

            // Lorry search functionality for both modals
            $('#lorrySearchStockIn, #lorrySearchStockOut').on('keyup', function () {
                var searchTerm = $(this).val().toLowerCase();
                var targetList = $(this).closest('.dropdown-menu').find('.form-check');
                
                targetList.each(function () {
                    var lorryName = $(this).find('label').text().toLowerCase();
                    if (lorryName.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Product selection for both modals
            $('.product-item').on('click', function(e) {
                e.preventDefault();
                var productName = $(this).text();
                var productId = $(this).data('value');
                var dropdown = $(this).closest('.dropdown-menu');
                
                // Update UI
                $(this).siblings().removeClass('active');
                $(this).addClass('active');
                dropdown.prev('.dropdown-toggle').text(productName);
                
                // Set hidden input value
                var modalId = $(this).closest('.modal').attr('id');
                if (modalId === 'stockin') {
                    $('#selectedProductStockIn').val(productId);
                } else {
                    $('#selectedProductStockOut').val(productId);
                    getstock(); // Trigger stock calculation for stockout
                }
            });

            // Product search functionality for both modals
            $('#productSearchStockIn, #productSearchStockOut').on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase();
                var productList = $(this).siblings('.list-group');
                
                productList.find('.product-item').each(function() {
                    var productText = $(this).text().toLowerCase();
                    if (productText.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Initialize dropdown buttons with default text
            $('.dropdown-toggle').each(function() {
                if (!$(this).text().trim()) {
                    if ($(this).closest('.dropdown-menu').find('.form-check').length) {
                        $(this).text('Select Lorries');
                    } else {
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
        $('#stockout').on('change', '.lorry-checkbox', function(){
            if ($('#selectedProductStockOut').val()) {
                getstock();
            }
        });

        function getstock() {
            // Get selected lorry IDs from checkboxes
            var lorryIds = [];
            $('#stockout').find('.lorry-checkbox:checked').each(function() {
                lorryIds.push($(this).val());
            });
            
            // Get selected product ID
            var productId = $('#selectedProductStockOut').val();

            if (lorryIds.length > 0 && productId) {
                ShowLoad();
                $.ajax({
                    url: '{{ ENV("APP_URL") }}' + '/inventoryBalances/getstock/' + lorryIds.join(',') + '/' + productId,
                    type: 'GET',
                    success: function(data) {
                        if (data.status) {
                            $('#stockout').find('#quantity').prop('disabled', false);
                            $('#stockout').find('#quantity').val(data.quantity);
                        } else {
                            $('#stockout').find('#quantity').prop('disabled', true);
                            $('#stockout').find('#quantity').val(data.quantity);
                            noti('e', 'Warning', data.message);
                        }
                        HideLoad();
                    },
                    error: function(error) {
                        noti('e', 'Please contact your administrator', error.responseJSON.message);
                        HideLoad();
                    }
                });
            }
        }
    </script>
@endpush