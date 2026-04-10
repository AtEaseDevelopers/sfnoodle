@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">{{ __('Stock Return') }}</li>
    </ol>
    <div class="container-fluid">
        <div class="animated fadeIn">
            @include('flash::message')
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-align-justify"></i>
                            {{ __('Stock Return') }}
                        </div>
                        <div class="card-body">
                            @include('inventory_returns.table')
                            <div class="pull-right mr-3">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Return Modal -->
    <div id="createRequest" class="modal fade" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                    <h4 class="modal-title h5 font-weight-bold mb-0">
                        <i class="fa fa-arrow-circle-up mr-2"></i>{{ __('Create Stock Return') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body p-4">
                    {!! Form::open(['route' => 'inventoryReturns.store', 'enctype' => 'multipart/form-data', 'id' => 'createReturnForm']) !!}
                    
                    <div class="row">
                        <!-- Driver Selection -->
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label for="driver_id" class="col-form-label font-weight-bold mb-2">{{ __('Driver') }} <span class="text-danger">*</span>:</label>
                                <div class="dropdown w-100">
                                    <button class="btn btn-outline-primary btn-block dropdown-toggle text-left py-2 px-3" type="button" id="dropdownDriverCreate" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 8px; font-size: 1rem;">
                                        <i class="fa fa-user mr-2"></i>{{ __('Select Driver') }}
                                    </button>
                                    <div class="dropdown-menu p-3 shadow-sm border-0" aria-labelledby="dropdownDriverCreate" style="width: 400px; max-height: 400px; overflow-y: auto; border-radius: 12px;">
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="driverSearchCreate" placeholder="Search Drivers...">
                                        </div>
                                        <div id="driverListCreate" class="list-group">
                                            @foreach($drivers as $driver)
                                                <a href="#" class="list-group-item list-group-item-action driver-item border-0 py-2 px-3 rounded mb-1" data-value="{{ $driver->id }}">
                                                    <i class="fa fa-user-circle-o mr-2 text-primary"></i>{{ $driver->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="driver_id" id="selectedDriverCreate" required>
                                <div class="text-danger small mt-1" id="driverError"></div>
                            </div>
                        </div>
                        
                        <!-- Remarks -->
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label for="remarks" class="col-form-label font-weight-bold mb-2">{{ __('Remarks') }} ({{ __('Optional') }}):</label>
                                <textarea class="form-control py-2" name="remarks" id="remarks" rows="2" placeholder="Any additional notes..." style="border-radius: 8px; resize: vertical;"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Table -->
                    <div class="form-group mt-2">
                        <label class="col-form-label font-weight-bold mb-3">{{ __('Items') }} <span class="text-danger">*</span>:</label>
                        <div class="table-responsive border rounded-lg" style="border-radius: 2px !important;">
                            <table class="table table-bordered mb-0" id="itemsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%" class="text-center align-middle">#</th>
                                        <th width="45%" class="align-middle">Product <span class="text-danger">*</span></th>
                                        <th width="20%" class="align-middle">Quantity <span class="text-danger">*</span></th>
                                        <th width="15%" class="text-center align-middle">Available</th>
                                        <th width="15%" class="text-center align-middle">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <!-- Items will be added here dynamically -->
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="5" class="text-right p-2">
                                            <button type="button" class="btn btn-success btn-sm px-3 py-1 rounded-pill" id="addItemBtn">
                                                <i class="fa fa-plus mr-1"></i> Add Item
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="text-danger small mt-1" id="itemsError"></div>
                    </div>

                    <div class="modal-footer border-0 px-0 pb-0 pt-4">
                        <button type="button" class="btn btn-secondary px-4 py-2 rounded-pill" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm">{{ __('Submit Return') }}</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Return Modal -->
    <div id="editRequest" class="modal fade" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-warning text-dark py-3">
                    <h4 class="modal-title h5 font-weight-bold mb-0">
                        <i class="fa fa-pencil-square-o mr-2"></i>{{ __('Edit Stock Return') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body p-4">
                    {!! Form::open(['route' => ['inventoryReturns.update', ':id'], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'id' => 'editReturnForm']) !!}
                    
                    <div class="row">
                        <!-- Driver Selection -->
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label for="driver_id" class="col-form-label font-weight-bold mb-2">{{ __('Driver') }} <span class="text-danger">*</span>:</label>
                                <div class="dropdown w-100">
                                    <button class="btn btn-outline-primary btn-block dropdown-toggle text-left py-2 px-3" type="button" id="dropdownDriverEdit" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 8px; font-size: 1rem;">
                                        <i class="fa fa-user mr-2"></i>{{ __('Select Driver') }}
                                    </button>
                                    <div class="dropdown-menu p-3 shadow-sm border-0" aria-labelledby="dropdownDriverEdit" style="width: 400px; max-height: 400px; overflow-y: auto; border-radius: 12px;">
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="driverSearchEdit" placeholder="Search Drivers...">
                                        </div>
                                        <div id="driverListEdit" class="list-group">
                                            @foreach($drivers as $driver)
                                                <a href="#" class="list-group-item list-group-item-action driver-item border-0 py-2 px-3 rounded mb-1" data-value="{{ $driver->id }}">
                                                    <i class="fa fa-user-circle-o mr-2 text-primary"></i>{{ $driver->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="driver_id" id="selectedDriverEdit" required>
                                <div class="text-danger small mt-1" id="driverEditError"></div>
                            </div>
                        </div>
                        
                        <!-- Remarks -->
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label for="remarks" class="col-form-label font-weight-bold mb-2">{{ __('Remarks') }} ({{ __('Optional') }}):</label>
                                <textarea class="form-control py-2" name="remarks" id="remarksEdit" rows="2" placeholder="Any additional notes..." style="border-radius: 8px; resize: vertical;"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Table for Edit -->
                    <div class="form-group mt-2">
                        <label class="col-form-label font-weight-bold mb-3">{{ __('Items') }} <span class="text-danger">*</span>:</label>
                        <div class="table-responsive border rounded-lg" style="border-radius: 12px !important;">
                            <table class="table table-bordered mb-0" id="editItemsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%" class="text-center align-middle">#</th>
                                        <th width="50%" class="align-middle">Product <span class="text-danger">*</span></th>
                                        <th width="25%" class="align-middle">Quantity <span class="text-danger">*</span></th>
                                        <th width="20%" class="text-center align-middle">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="editItemsBody">
                                    <!-- Items will be populated here -->
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="4" class="text-right p-2">
                                            <button type="button" class="btn btn-success btn-sm px-3 py-1 rounded-pill" id="addEditItemBtn">
                                                <i class="fa fa-plus mr-1"></i> Add Item
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="text-danger small mt-1" id="editItemsError"></div>
                    </div>

                    <div class="modal-footer border-0 px-0 pb-0 pt-4">
                        <button type="button" class="btn btn-secondary px-4 py-2 rounded-pill" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm">{{ __('Update Return') }}</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Return Modal -->
    <div id="viewRequest" class="modal fade">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-info py-3">
                    <h4 class="modal-title h5 font-weight-bold mb-0">
                        <i class="fa fa-info-circle mr-2"></i>Stock Return Details <span id="viewRequestId"></span>
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card bg-light border-0 shadow-none">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <dl class="row mb-0">
                                                <dt class="col-sm-5 font-weight-bold">Return ID:</dt>
                                                <dd class="col-sm-7" id="viewRequestIdText">—</dd>
                                                
                                                <dt class="col-sm-5 font-weight-bold">Driver:</dt>
                                                <dd class="col-sm-7" id="viewDriverName">—</dd>
                                                
                                                <dt class="col-sm-5 font-weight-bold">Status:</dt>
                                                <dd class="col-sm-7"><span id="viewStatusBadge"></span></dd>
                                            </dl>
                                        </div>
                                        <div class="col-md-6">
                                            <dl class="row mb-0">
                                                <dt class="col-sm-5 font-weight-bold">Returned At:</dt>
                                                <dd class="col-sm-7" id="viewCreatedAt">—</dd>
                                                
                                                <dt class="col-sm-5 font-weight-bold">Remarks:</dt>
                                                <dd class="col-sm-7" id="viewRemarks">—</dd>
                                            </dl>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row">
                                        <div class="col-md-6" id="viewApprovedSection" style="display: none;">
                                            <dl class="row mb-0">
                                                <dt class="col-sm-5 font-weight-bold">Approved By:</dt>
                                                <dd class="col-sm-7" id="viewApprovedBy">—</dd>
                                            </dl>
                                        </div>
                                        <div class="col-md-6" id="viewApprovedAtSection" style="display: none;">
                                            <dl class="row mb-0">
                                                <dt class="col-sm-5 font-weight-bold">Approved At:</dt>
                                                <dd class="col-sm-7" id="viewApprovedAt">—</dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Table Section -->
                    <div class="mt-4">
                        <h5 class="font-weight-bold mb-3"><i class="fa fa-list mr-2"></i>Returned Items</h5>
                        <div id="viewItemsTable"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <div class="d-flex justify-content-between w-100">
                        <div id="viewActionButtons">
                            <!-- Action buttons will be shown here for pending returns only -->
                        </div>
                        <button type="button" class="btn btn-secondary px-4 py-2 rounded-pill" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        /* Enhanced styles - matching Stock Return */
        .modal-dialog {
            margin: 1.75rem auto;
        }
        
        .modal-xl {
            max-width: 1400px;
        }
        
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: visible !important;
        }
        
        .modal-header {
            padding: 1.25rem 1.5rem;
            flex-shrink: 0;
        }
    
        .modal-footer {
            padding: 1rem 1.5rem;
            flex-shrink: 0;
        }
        
        /* Critical fix for dropdown - make it appear above everything */
        .dropdown {
            position: relative;
        }
        
        .dropdown-menu {
            position: absolute !important;
            z-index: 9999 !important;
            max-height: 350px;
            width: 400px;
            overflow-y: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #e0e0e0;
            background: white;
            will-change: transform;
        }
        
        /* Ensure dropdown appears below the button by default */
        .item-row .dropdown-menu,
        .edit-item-row .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            right: auto !important;
            margin-top: 5px !important;
            margin-bottom: 0 !important;
            bottom: auto !important;
        }
        
        /* Fix for table responsive - prevent cutoff */
        .table-responsive {
            overflow-x: auto !important;
            overflow-y: visible !important;
        }
        
        .table-responsive .table {
            overflow: visible !important;
            margin-bottom: 0;
        }
        
        .table-responsive .table tbody {
            overflow: visible !important;
        }
        
        .table-responsive .table tr {
            overflow: visible !important;
        }
        
        .table-responsive .table td {
            overflow: visible !important;
        }
        
        /* Make each row position relative for better dropdown positioning */
        .item-row, .edit-item-row {
            position: relative;
        }
        
        /* Remove overflow restrictions on containers */
        #itemsTable, #editItemsTable {
            overflow: visible !important;
        }
        
        .card-body {
            overflow: visible !important;
        }
        
        /* Ensure modal doesn't clip dropdowns */
        .modal {
            overflow: visible !important;
        }
        
        .modal-dialog {
            overflow: visible !important;
        }
        
        .dropdown-toggle::after {
            margin-left: 10px;
            float: right;
            margin-top: 8px;
        }
        
        .dropdown-menu::-webkit-scrollbar {
            width: 6px;
        }
        
        .dropdown-menu::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .dropdown-menu::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        .btn-outline-primary {
            border-color: #007bff;
            color: #007bff;
        }
        
        .btn-outline-primary:hover {
            background-color: #007bff;
            color: #fff;
        }
        
        .btn-outline-secondary {
            border-color: #6c757d;
            color: #6c757d;
        }
        
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }
        
        .driver-item:hover,
        .driver-item.active,
        .product-select-item:hover,
        .product-select-item.active,
        .edit-product-select-item:hover,
        .edit-product-select-item.active {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        
        .list-group-item {
            border: 1px solid rgba(0,0,0,.125);
            margin-bottom: -1px;
            padding: 10px 15px;
        }
        
        .list-group-item:first-child {
            border-top-left-radius: 6px;
            border-top-right-radius: 6px;
        }
        
        .list-group-item:last-child {
            border-bottom-left-radius: 6px;
            border-bottom-right-radius: 6px;
        }
        
        .badge {
            font-size: 85%;
            padding: 5px 10px;
            border-radius: 4px;
        }
        
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-approved {
            background-color: #28a745;
            color: white;
        }
        
        .badge-rejected {
            background-color: #dc3545;
            color: white;
        }
        
        .badge-cancelled {
            background-color: #6c757d;
            color: white;
        }
        
        .small {
            font-size: 80%;
        }
        
        .item-row td, .edit-item-row td {
            overflow: visible !important;
            position: static !important;
        }
        
        .item-row .dropdown, .edit-item-row .dropdown {
            position: static !important;
        }

        /* Make dropdown menu appear above everything with proper positioning */
        .item-row .dropdown-menu,
        .edit-item-row .dropdown-menu {
            position: fixed !important;
            top: auto !important;
            left: auto !important;
            z-index: 9999 !important;
            min-width: 600px;
            max-width: 600px;
            margin-top: 0 !important;
        }

        /* Ensure table container doesn't clip dropdowns */
        .table-responsive {
            overflow: visible !important;
        }

        .table-responsive .table {
            overflow: visible !important;
        }

        .table-responsive .table tbody {
            overflow: visible !important;
        }

        .table-responsive .table tr {
            overflow: visible !important;
        }

        /* Ensure modal body doesn't restrict dropdowns */
        .modal-body {
            overflow: visible !important;
        }
        /* Button styling */
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
        }
        
        /* Error styling */
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .text-danger {
            font-size: 0.75rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .modal-xl {
                max-width: 95%;
                margin: 1rem auto;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
        }
        
        /* View modal table styling */
        #viewItemsTable .table {
            margin-bottom: 0;
        }
        
        #viewItemsTable .table th {
            background-color: #f8f9fa;
        }
        
        /* Ensure dropdowns from table rows are visible above modal content */
        .item-row .dropdown.show,
        .edit-item-row .dropdown.show {
            position: relative;
            z-index: 10000;
        }
        
        /* Fix for any hidden overflow on parent elements */
        .container-fluid, .animated, .fadeIn, .row, .col-lg-12, .card, .card-body {
            overflow: visible !important;
        }
    </style>

<script>
    $(document).ready(function () {
        // Initialize DataTable
         var table = window.LaravelDataTables["dataTableBuilder"] || $('.data-table').DataTable();
        
        // Store current request ID for actions
        var currentRequestId = null;
        var currentRequestStatus = null;
        
        // Items counters for create and edit modals
        var itemCounter = 0;
        var editItemCounter = 0;
        
        // Products data from server
        var products = @json($products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code
            ];
        }));
        
        // Create a products lookup object
        var productsLookup = {};
        products.forEach(function(product) {
            productsLookup[product.id] = product;
        });
        
        // Store driver inventory data
        var driverInventory = [];
        var driverInventoryMap = {};
        
        // ============================================
        // DRIVER SEARCH FUNCTIONALITY
        // ============================================
        
        // Driver search functionality for create modal
        $('#driverSearchCreate').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            $('#driverListCreate .driver-item').each(function() {
                var driverName = $(this).text().toLowerCase();
                if (driverName.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Driver search functionality for edit modal
        $('#driverSearchEdit').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            $('#driverListEdit .driver-item').each(function() {
                var driverName = $(this).text().toLowerCase();
                if (driverName.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Clear search when modal closes
        $('#createRequest').on('hidden.bs.modal', function () {
            $('#driverSearchCreate').val('');
            $('#driverListCreate .driver-item').show();
        });

        $('#editRequest').on('hidden.bs.modal', function () {
            $('#driverSearchEdit').val('');
            $('#driverListEdit .driver-item').show();
        });
        
        // ============================================
        // CREATE MODAL FUNCTIONS
        // ============================================
        
        // Initialize create modal items table
        function initializeItemsTable() {
            $('#itemsBody').empty();
            itemCounter = 0;
            addItemRow();
        }
        
        // Function to position dropdown relative to button (using fixed positioning)
        function positionProductDropdown(button, menu) {
            if (!button || !menu) return;
            
            var buttonRect = button.getBoundingClientRect();
            var menuHeight = 350;
            var viewportHeight = window.innerHeight;
            var spaceBelow = viewportHeight - buttonRect.bottom;
            var spaceAbove = buttonRect.top;
            
            // Reset styles
            menu.style.position = 'fixed';
            menu.style.minWidth = '320px';
            menu.style.maxWidth = '400px';
            
            // Determine vertical position
            if (spaceBelow < menuHeight && spaceAbove > menuHeight) {
                // Show above the button
                menu.style.top = (buttonRect.top - menuHeight) + 'px';
                menu.style.bottom = 'auto';
            } else {
                // Show below the button
                menu.style.top = (buttonRect.bottom + 5) + 'px';
                menu.style.bottom = 'auto';
            }
            
            // Horizontal positioning
            var menuWidth = 320;
            var viewportWidth = window.innerWidth;
            if (buttonRect.left + menuWidth > viewportWidth) {
                menu.style.left = (viewportWidth - menuWidth - 10) + 'px';
                menu.style.right = 'auto';
            } else {
                menu.style.left = buttonRect.left + 'px';
                menu.style.right = 'auto';
            }
        }

        // Handle dropdown show event for product dropdowns
        $(document).on('show.bs.dropdown', '.item-row .dropdown, .edit-item-row .dropdown', function(e) {
            var button = $(this).find('.dropdown-toggle')[0];
            var menu = $(this).find('.dropdown-menu')[0];
            
            if (button && menu) {
                setTimeout(function() {
                    positionProductDropdown(button, menu);
                }, 10);
            }
        });

        // Reposition on window scroll/resize
        $(window).on('scroll resize', function() {
            $('.dropdown-menu.show').each(function() {
                var parentDropdown = $(this).closest('.dropdown');
                var button = parentDropdown.find('.dropdown-toggle')[0];
                if (button && $(this).is(':visible')) {
                    positionProductDropdown(button, this);
                }
            });
        });
        
        // Add item row to create modal
        function addItemRow() {
            var row = `
                <tr class="item-row" data-index="${itemCounter}">
                    <td class="align-middle text-center font-weight-bold">${itemCounter + 1}</td>
                    <td style="min-width: 280px;">
                        <div class="dropdown w-100">
                            <button class="btn btn-outline-secondary btn-block dropdown-toggle text-left product-dropdown py-2" type="button" id="productDropdown${itemCounter}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 8px; white-space: normal; word-wrap: break-word;">
                                <i class="fa fa-cube mr-2"></i>{{ __('Select Product') }}
                            </button>
                            <div class="dropdown-menu p-3 shadow" aria-labelledby="productDropdown${itemCounter}" style="width: 100%; min-width: 400px; max-height: 400px; overflow-y: auto;">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control product-search" placeholder="Search Products..." data-index="${itemCounter}">
                                </div>
                                <div class="product-list" data-index="${itemCounter}" style="max-height: 220px; overflow-y: auto;">
                                    @foreach($products as $product)
                                        <a href="#" class="list-group-item list-group-item-action product-select-item border-0 py-2 rounded mb-1" data-index="${itemCounter}" data-value="{{ $product->id }}" data-name="{{ $product->name }}" data-quantity="0">
                                            <i class="fa fa-cube mr-2 text-secondary"></i>{{ $product->name }} ({{ $product->code }})
                                            <span class="product-available-qty text-muted float-right"></span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="product-id-input" name="items[${itemCounter}][product_id]" value="">
                        <div class="text-danger product-error small mt-1"></div>
                    </td>
                    <td style="min-width: 140px;">
                        <input type="number" min="1" class="form-control quantity-input" name="items[${itemCounter}][quantity]" placeholder="Enter quantity" style="border-radius: 8px;">
                        <div class="text-danger quantity-error small mt-1"></div>
                    </td>
                    <td class="align-middle text-center">
                        <span class="available-quantity" id="availableQuantity${itemCounter}">—</span>
                    </td>
                    <td class="align-middle text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill remove-item-btn" ${itemCounter === 0 ? 'disabled' : ''}>
                            <i class="fa fa-trash-o mr-1"></i> Remove
                        </button>
                    </td>
                </tr>
            `;
            
            $('#itemsBody').append(row);
            itemCounter++;
            
            // Enable remove buttons if more than one row
            if ($('#itemsBody tr').length > 1) {
                $('#itemsBody tr:first .remove-item-btn').prop('disabled', false);
            }
        }
        
        // Function to update product quantities in dropdown lists
        function updateProductQuantitiesInDropdown(inventoryMap) {
            // Update create modal product lists
            $('.product-list .product-select-item').each(function() {
                var productId = $(this).data('value');
                var quantity = inventoryMap[productId] || 0;
                var productName = $(this).data('name');
                var productCode = $(this).text().match(/\(([^)]+)\)/)?.[1] || '';
                
                $(this).html(`<i class="fa fa-cube mr-2 text-secondary"></i>${productName} (${productCode}) <span class="badge badge-info float-right mt-1">Available: ${quantity}</span>`);
                $(this).data('quantity', quantity);
                
                if (quantity === 0) {
                    $(this).hide();
                } else if (quantity < 10) {
                    $(this).removeClass('text-muted').addClass('text-warning');
                } else {
                    $(this).removeClass('text-muted text-warning');
                }
            });
            
            // Update edit modal product lists
            $('.edit-product-list .edit-product-select-item').each(function() {
                var productId = $(this).data('value');
                var quantity = inventoryMap[productId] || 0;
                var productName = $(this).data('name');
                var productCode = $(this).text().match(/\(([^)]+)\)/)?.[1] || '';
                
                $(this).html(`<i class="fa fa-cube mr-2 text-secondary"></i>${productName} (${productCode}) <span class="badge badge-info float-right mt-1">Available: ${quantity}</span>`);
                $(this).data('quantity', quantity);
                
                if (quantity === 0) {
                    $(this).addClass('text-muted');
                } else if (quantity < 10) {
                    $(this).addClass('text-warning');
                } else {
                    $(this).removeClass('text-muted text-warning');
                }
            });
        }
        
        // Driver selection for create modal - fetch inventory when driver selected
        $(document).on('click', '#driverListCreate .driver-item', function(e) {
            e.preventDefault();
            var driverName = $(this).text().trim();
            var driverId = $(this).data('value');
            
            // Remove active class and background from all driver items
            $('#driverListCreate .driver-item').removeClass('active bg-primary text-white');
            $('#driverListCreate .driver-item').css('background', '');
            
            // Add active class to selected item
            $(this).addClass('active bg-primary text-white');
            
            // Update the dropdown button text
            $('#dropdownDriverCreate').html(`<i class="fa fa-user mr-2"></i>${driverName}`);
            $('#selectedDriverCreate').val(driverId);
            $('#driverError').text('');
            
            fetchDriverInventory(driverId);
        });
        
        // Fetch driver inventory
        function fetchDriverInventory(driverId) {
            if (typeof ShowLoad === 'function') ShowLoad();
            $.ajax({
                url: '{{ route("inventory.getDriverInventory") }}',
                type: 'GET',
                data: { driver_id: driverId },
                dataType: 'json',
                success: function(response) {
                    if (typeof HideLoad === 'function') HideLoad();
                    if (response.success) {
                        driverInventory = response.inventory;
                        
                        driverInventoryMap = {};
                        driverInventory.forEach(function(item) {
                            driverInventoryMap[item.product_id] = item.quantity;
                        });
                        
                        $('#itemsBody tr').each(function() {
                            var productId = $(this).find('.product-id-input').val();
                            if (productId) {
                                var available = getAvailableQuantity(productId);
                                var index = $(this).data('index');
                                $('#availableQuantity' + index).html(available);
                                $(this).find('.quantity-input').attr('max', available);
                            }
                        });
                        
                        updateProductQuantitiesInDropdown(driverInventoryMap);
                    }
                },
                error: function() {
                    if (typeof HideLoad === 'function') HideLoad();
                    driverInventory = [];
                    driverInventoryMap = {};
                }
            });
        }
        
        // Get available quantity for a product
        function getAvailableQuantity(productId) {
            return driverInventoryMap[productId] || 0;
        }
        
        // Product selection in create modal
        $(document).on('click', '.product-select-item', function(e) {
            e.preventDefault();
            var index = $(this).data('index');
            var productId = $(this).data('value');
            var productName = $(this).data('name');
            var availableQuantity = $(this).data('quantity') || 0;
            var driverId = $('#selectedDriverCreate').val();
            
            if (!driverId) {
                alert('Please select a driver first');
                return;
            }
            
            if (availableQuantity === 0) {
                alert('This product is out of stock for the selected driver');
                return;
            }
            
            $('#productDropdown' + index).html(`<i class="fa fa-cube mr-2"></i>${productName}`).attr('title', productName);
            $(this).closest('tr').find('.product-id-input').val(productId);
            $(this).closest('tr').find('.product-error').text('');
            $(this).siblings().removeClass('active');
            $(this).addClass('active bg-light');
            $('#availableQuantity' + index).html(availableQuantity);
            $(this).closest('tr').find('.quantity-input').attr('max', availableQuantity);
            
            var currentQuantity = $(this).closest('tr').find('.quantity-input').val();
            if (currentQuantity && parseInt(currentQuantity) > availableQuantity) {
                $(this).closest('tr').find('.quantity-input').val(availableQuantity);
                $(this).closest('tr').find('.quantity-error').text('Quantity adjusted to available stock: ' + availableQuantity);
            }
        });
        
        // Validate quantity on input change
        $(document).on('change keyup', '.quantity-input', function() {
            var row = $(this).closest('tr');
            var productId = row.find('.product-id-input').val();
            var quantity = parseInt($(this).val());
            var available = getAvailableQuantity(productId);
            var quantityError = row.find('.quantity-error');
            
            if (productId && quantity) {
                if (quantity > available) {
                    quantityError.text('⚠️ Quantity exceeds available stock. Maximum: ' + available);
                    $(this).addClass('is-invalid');
                } else if (quantity < 1) {
                    quantityError.text('⚠️ Quantity must be at least 1');
                    $(this).addClass('is-invalid');
                } else {
                    quantityError.text('');
                    $(this).removeClass('is-invalid');
                }
            }
        });
        
        // Product search in create modal
        $(document).on('keyup', '.product-search', function() {
            var searchTerm = $(this).val().toLowerCase();
            var index = $(this).data('index');
           // Find the product list within the same dropdown menu
            var dropdownMenu = $(this).closest('.dropdown-menu');
            var productList = dropdownMenu.find('.product-list');
            
            productList.find('.product-select-item').each(function() {
                var productText = $(this).text().toLowerCase();
                if (productText.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Add item button
        $('#addItemBtn').on('click', function() {
            addItemRow();
        });
        
        // Remove item button
        $(document).on('click', '.remove-item-btn', function() {
            if ($('#itemsBody tr').length > 1) {
                var row = $(this).closest('tr');
                row.remove();
                
                $('#itemsBody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                    $(this).attr('data-index', index);
                    
                    var dropdownBtn = $(this).find('.product-dropdown');
                    var dropdownId = 'productDropdown' + index;
                    dropdownBtn.attr('id', dropdownId).attr('aria-labelledby', dropdownId);
                    
                    $(this).find('.product-search, .product-list').attr('data-index', index);
                    $(this).find('.product-select-item').attr('data-index', index);
                    
                    $(this).find('.product-id-input, .quantity-input').each(function() {
                        var name = $(this).attr('name');
                        if (name && name.includes('items[')) {
                            var newName = name.replace(/items\[\d+\]/, 'items[' + index + ']');
                            $(this).attr('name', newName);
                        }
                    });
                    
                    $(this).find('.available-quantity').attr('id', 'availableQuantity' + index);
                });
                
                itemCounter = $('#itemsBody tr').length;
                
                if ($('#itemsBody tr').length === 1) {
                    $('#itemsBody tr:first .remove-item-btn').prop('disabled', true);
                }
            }
        });
        
        // Clear create modal when opened
        $('#createRequest').on('show.bs.modal', function () {
            initializeItemsTable();
            $('#createReturnForm')[0].reset();
            $('#selectedDriverCreate').val('');
            $('#dropdownDriverCreate').html('<i class="fa fa-user mr-2"></i>{{ __('Select Driver') }}');
            // Clear all active classes from driver items
            $('#driverListCreate .driver-item').removeClass('active bg-primary text-white');
            $('#driverListCreate .driver-item').css('background', '');
            $('#driverError, #itemsError').text('');
            $('#remarks').val('');
            driverInventory = [];
            driverInventoryMap = {};
        });
        
        // ============================================
        // EDIT MODAL FUNCTIONS
        // ============================================
        
        // Initialize edit modal items table
        function initializeEditItemsTable() {
            $('#editItemsBody').empty();
            editItemCounter = 0;
            addEditItemRow();
        }
        
        // Add item row to edit modal
        function addEditItemRow(productId = '', productName = '', quantity = '') {
            var displayName = productName || '{{ __('Select Product') }}';
            var row = `
                <tr class="edit-item-row" data-index="${editItemCounter}">
                    <td class="align-middle text-center font-weight-bold">${editItemCounter + 1}</td>
                    <td style="min-width: 300px;">
                        <div class="dropdown w-100">
                            <button class="btn btn-outline-secondary btn-block dropdown-toggle text-left edit-product-dropdown py-2" type="button" id="editProductDropdown${editItemCounter}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 8px; white-space: normal; word-wrap: break-word;">
                                <i class="fa fa-cube mr-2"></i>${displayName}
                            </button>
                            <div class="dropdown-menu p-3 shadow" aria-labelledby="editProductDropdown${editItemCounter}" style="width: 100%; min-width: 400px; max-height: 400px; overflow-y: auto;">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control edit-product-search" placeholder="Search Products..." data-index="${editItemCounter}">
                                </div>
                                <div class="edit-product-list" data-index="${editItemCounter}" style="max-height: 220px; overflow-y: auto;">
                                    @foreach($products as $product)
                                        <a href="#" class="list-group-item list-group-item-action edit-product-select-item border-0 py-2 rounded mb-1" data-index="${editItemCounter}" data-value="{{ $product->id }}" data-name="{{ $product->name }}" data-quantity="0">
                                            <i class="fa fa-cube mr-2 text-secondary"></i>{{ $product->name }} ({{ $product->code }})
                                            <span class="product-available-qty text-muted float-right"></span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="edit-product-id-input" name="items[${editItemCounter}][product_id]" value="${productId}">
                        <div class="text-danger edit-product-error small mt-1"></div>
                    </td>
                    <td style="min-width: 150px;">
                        <input type="number" min="1" class="form-control edit-quantity-input" name="items[${editItemCounter}][quantity]" placeholder="Enter quantity" value="${quantity}" style="border-radius: 8px;">
                        <div class="text-danger edit-quantity-error small mt-1"></div>
                    </td>
                    <td class="align-middle text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill remove-edit-item-btn" ${editItemCounter === 0 ? 'disabled' : ''}>
                            <i class="fa fa-trash-o mr-1"></i> Remove
                        </button>
                    </td>
                </tr>
            `;
            
            $('#editItemsBody').append(row);
            editItemCounter++;
            
            if ($('#editItemsBody tr').length > 1) {
                $('#editItemsBody tr:first .remove-edit-item-btn').prop('disabled', false);
            }
        }
        
        // Handle edit modal opening
        $(document).on('click', '.edit-return-btn', function(e) {
            e.preventDefault();
            var requestId = $(this).data('id');
            var requestData = $(this).data('request');
            
            if (typeof requestData === 'string') {
                requestData = JSON.parse(requestData);
            }
            
            currentRequestId = requestId;
            
            var formAction = '{{ route("inventoryReturns.update", ":id") }}';
            formAction = formAction.replace(':id', requestId);
            $('#editReturnForm').attr('action', formAction);
            
            $('#selectedDriverEdit').val(requestData.driver_id);
            $('#dropdownDriverEdit').html(`<i class="fa fa-user mr-2"></i>${requestData.driver_name || 'Select Driver'}`);
            $('#remarksEdit').val(requestData.remarks || '');
            
            $('#driverListEdit .driver-item').removeClass('active');
            $('#driverListEdit .driver-item[data-value="' + requestData.driver_id + '"]').addClass('active bg-primary text-white');
            
            initializeEditItemsTable();
            
            if (requestData.items && Array.isArray(requestData.items) && requestData.items.length > 0) {
                $('#editItemsBody').empty();
                editItemCounter = 0;
                
                requestData.items.forEach(function(item, index) {
                    var productName = getProductName(item.product_id);
                    addEditItemRow(item.product_id, productName, item.quantity);
                });
            } else {
                if (requestData.product_id && requestData.quantity) {
                    $('#editItemsBody').empty();
                    editItemCounter = 0;
                    var productName = getProductName(requestData.product_id);
                    addEditItemRow(requestData.product_id, productName, requestData.quantity);
                }
            }
            
            $('#editRequest').modal('show');
        });
        
        // Driver selection for edit modal
        $(document).on('click', '#driverListEdit .driver-item', function(e) {
            e.preventDefault();
            var driverName = $(this).text().trim();
            var driverId = $(this).data('value');
            
            // Remove active class and background from all driver items
            $('#driverListEdit .driver-item').removeClass('active bg-primary text-white');
            $('#driverListEdit .driver-item').css('background', '');
            
            // Add active class to selected item
            $(this).addClass('active bg-primary text-white');
            
            // Update the dropdown button text
            $('#dropdownDriverEdit').html(`<i class="fa fa-user mr-2"></i>${driverName}`);
            $('#selectedDriverEdit').val(driverId);
            $('#driverEditError').text('');
        });
        
        // Product search in edit modal
        $(document).on('keyup', '.edit-product-search', function() {
            var searchTerm = $(this).val().toLowerCase();
            var index = $(this).data('index');
            var productList = $(this).siblings('.edit-product-list');
            
            productList.find('.edit-product-select-item').each(function() {
                var productText = $(this).text().toLowerCase();
                if (productText.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Product selection in edit modal
        $(document).on('click', '.edit-product-select-item', function(e) {
            e.preventDefault();
            var index = $(this).data('index');
            var productId = $(this).data('value');
            var productName = $(this).data('name');
            
            $('#editProductDropdown' + index).html(`<i class="fa fa-cube mr-2"></i>${productName}`).attr('title', productName);
            $(this).closest('tr').find('.edit-product-id-input').val(productId);
            $(this).closest('tr').find('.edit-product-error').text('');
            $(this).siblings().removeClass('active');
            $(this).addClass('active bg-light');
        });
        
        // Add item button for edit modal
        $('#addEditItemBtn').on('click', function() {
            addEditItemRow();
        });
        
        // Remove item button for edit modal
        $(document).on('click', '.remove-edit-item-btn', function() {
            if ($('#editItemsBody tr').length > 1) {
                var row = $(this).closest('tr');
                row.remove();
                
                $('#editItemsBody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                    $(this).attr('data-index', index);
                    
                    var dropdownBtn = $(this).find('.edit-product-dropdown');
                    var dropdownId = 'editProductDropdown' + index;
                    dropdownBtn.attr('id', dropdownId).attr('aria-labelledby', dropdownId);
                    
                    $(this).find('.edit-product-search, .edit-product-list').attr('data-index', index);
                    $(this).find('.edit-product-select-item').attr('data-index', index);
                    
                    $(this).find('.edit-product-id-input, .edit-quantity-input').each(function() {
                        var name = $(this).attr('name');
                        if (name && name.includes('items[')) {
                            var newName = name.replace(/items\[\d+\]/, 'items[' + index + ']');
                            $(this).attr('name', newName);
                        }
                    });
                });
                
                editItemCounter = $('#editItemsBody tr').length;
                
                if ($('#editItemsBody tr').length === 1) {
                    $('#editItemsBody tr:first .remove-edit-item-btn').prop('disabled', true);
                }
            }
        });
        
        // ============================================
        // VIEW MODAL FUNCTIONS
        // ============================================
        
        // Helper function to get product name
        function getProductName(productId) {
            var product = productsLookup[productId];
            return product ? product.name : 'Product ' + productId;
        }
        
        // Handle view modal opening
        $(document).on('click', '.view-return-btn', function(e) {
            e.preventDefault();
            var requestId = $(this).data('id');
            var requestData = $(this).data('request');
            
            if (typeof requestData === 'string') {
                requestData = JSON.parse(requestData);
            }
            
            currentRequestId = requestId;
            currentRequestStatus = requestData.status;
            
            $('#viewRequestId').text('(#' + requestId + ')');
            $('#viewRequestIdText').text(requestId);
            
            $('#viewDriverName').text(requestData.driver_name || 'N/A');
            $('#viewRemarks').text(requestData.remarks || 'No remarks');
            $('#viewCreatedAt').text(requestData.created_at || 'N/A');
            
            var status = requestData.status;
            var badgeClass = getStatusBadgeClass(status);
            $('#viewStatusBadge').html('<span class="badge ' + badgeClass + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>');
            
            // Show items table
            if (requestData.items && Array.isArray(requestData.items) && requestData.items.length > 0) {
                var itemsHtml = '<div class="table-responsive"><table class="table table-sm table-bordered">';
                itemsHtml += '<thead class="bg-light"><tr><th width="5%">#</th><th>Product</th><th width="20%" class="text-center">Returned Quantity</th></tr></thead><tbody>';
                
                var totalQuantity = 0;
                
                requestData.items.forEach(function(item, index) {
                    var quantity = item.quantity || 0;
                    var productCode = item.product_code;
                    var productName = item.product_name;

                    if (!productCode && item.product_id) {
                        var product = productsLookup[item.product_id];
                        productCode = product ? product.code : '';
                    }    
                    var displayText = productCode ? productCode : (productName || 'Unknown Product');

                    itemsHtml += '<tr>';
                    itemsHtml += '<td class="text-center">' + (index + 1) + '</td>';
                    itemsHtml += '<td>' + displayText  + '</td>'; 
                    itemsHtml += '<td class="text-center"><span class="badge badge-primary px-3 py-2 rounded-pill">' + quantity + '</span></td>';
                    itemsHtml += '</tr>';
                    
                    totalQuantity += parseInt(quantity);
                });
                
                itemsHtml += '</tbody>';
                itemsHtml += '<tfoot><tr class="bg-light">';
                itemsHtml += '<td colspan="2" class="text-right"><strong>Total Items:</strong></td>';
                itemsHtml += '<td class="text-center"><strong>' + totalQuantity + '</strong></td>';
                itemsHtml += '</tr></tfoot>';
                itemsHtml += '</table></div>';
                
                $('#viewItemsTable').html(itemsHtml);
            } else {
                var singleItemHtml = '<div class="table-responsive"><table class="table table-sm table-bordered">';
                singleItemHtml += '<thead class="bg-light"><tr><th width="5%">#</th><th>Product</th><th width="20%" class="text-center">Returned Quantity</th></tr></thead><tbody>';
                
                if (requestData.product_id && requestData.quantity) {
                    var productName = getProductName(requestData.product_id);
                    var productCode = requestData.product_code;
                    var displayText = productCode ? productCode : (productName || 'Unknown Product');   
                    singleItemHtml += '<tr>';
                    singleItemHtml += '<td class="text-center">1</td>';
                    singleItemHtml += '<td>' + displayText + '</td>'; 
                    singleItemHtml += '<td class="text-center"><span class="badge badge-primary px-3 py-2 rounded-pill">' + requestData.quantity + '</span></td>';
                    singleItemHtml += '</tr>';
                }
                
                singleItemHtml += '</tbody></table></div>';
                $('#viewItemsTable').html(singleItemHtml);
            }
            
            if (status === 'approved') {
                $('#viewApprovedSection').show();
                $('#viewApprovedAtSection').show();
                $('#viewApprovedBy').text(requestData.approved_by || 'N/A');
                $('#viewApprovedAt').text(requestData.approved_at || 'N/A');
            } else {
                $('#viewApprovedSection').hide();
                $('#viewApprovedAtSection').hide();
            }
            
            var actionButtonsHtml = '';
            if (status === 'pending') {
                actionButtonsHtml = `
                    <button type="button" class="btn btn-success mr-2 rounded-pill px-4" id="approveFromViewBtn">
                        <i class="fa fa-check mr-1"></i> Approve
                    </button>
                    <button type="button" class="btn btn-outline-danger rounded-pill px-4" id="rejectFromViewBtn">
                        <i class="fa fa-times mr-1"></i> Reject
                    </button>
                `;
            }
            $('#viewActionButtons').html(actionButtonsHtml);
            
            $('#viewRequest').modal('show');
        });
        
        // ============================================
        // FORM VALIDATION AND SUBMISSION
        // ============================================
        
        // Validate create form
        $('#createReturnForm').submit(function(e) {
            e.preventDefault();
            
            $('#driverError, #itemsError').text('');
            $('.product-error, .quantity-error').text('');
            
            var driverId = $('#selectedDriverCreate').val();
            if (!driverId) {
                $('#driverError').text('⚠️ Please select a driver');
                return false;
            }
            
            var hasErrors = false;
            var items = [];
            var productIds = new Set();
            
            $('#itemsBody tr').each(function(index) {
                var productId = $(this).find('.product-id-input').val();
                var quantity = $(this).find('.quantity-input').val();
                var productError = $(this).find('.product-error');
                var quantityError = $(this).find('.quantity-error');
                var available = getAvailableQuantity(productId);
                
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
                
                if (!quantity || quantity < 1) {
                    quantityError.text('⚠️ Please enter a valid quantity (minimum 1)');
                    hasErrors = true;
                } else if (available < quantity) {
                    quantityError.text('⚠️ Insufficient stock. Available: ' + available);
                    hasErrors = true;
                }
                
                if (productId && quantity && quantity >= 1 && available >= quantity) {
                    items.push({
                        product_id: parseInt(productId),
                        quantity: parseInt(quantity)
                    });
                }
            });
            
            if (items.length === 0) {
                $('#itemsError').text('⚠️ Please add at least one valid item with product selected and quantity entered');
                hasErrors = true;
            }
            
            if (hasErrors) {
                $('#itemsBody tr').each(function(index) {
                    var productId = $(this).find('.product-id-input').val();
                    var quantity = $(this).find('.quantity-input').val();
                    var available = getAvailableQuantity(productId);
                    
                    if (!productId) {
                        $(this).find('.product-dropdown').addClass('is-invalid');
                    } else {
                        $(this).find('.product-dropdown').removeClass('is-invalid');
                    }
                    
                    if (!quantity || quantity < 1 || (productId && available < quantity)) {
                        $(this).find('.quantity-input').addClass('is-invalid');
                    } else {
                        $(this).find('.quantity-input').removeClass('is-invalid');
                    }
                });
                return false;
            }
            
            var formData = {
                driver_id: driverId,
                items: items,
                remarks: $('#remarks').val(),
                _token: '{{ csrf_token() }}'
            };
            
            if (typeof ShowLoad === 'function') ShowLoad();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (typeof HideLoad === 'function') HideLoad();
                    if (response.success) {
                        $('#createRequest').modal('hide');
                        showNotification('success', response.message);
                        
                        if (table && typeof table.ajax !== 'undefined') {
                            table.ajax.reload(null, false);
                        } else if (table && typeof table.draw !== 'undefined') {
                            table.draw(false);
                        }
                    } else {
                        showNotification('error', response.message || 'An error occurred');
                        if (response.errors) {
                            for (var field in response.errors) {
                                if (response.errors.hasOwnProperty(field)) {
                                    showNotification('error', response.errors[field][0]);
                                }
                            }
                        }
                    }
                },
                error: function(xhr) {
                    if (typeof HideLoad === 'function') HideLoad();
                    var errorMessage = 'An error occurred';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            for (var field in errors) {
                                if (errors.hasOwnProperty(field)) {
                                    errorMessage = errors[field][0];
                                    break;
                                }
                            }
                        }
                    }
                    
                    showNotification('error', errorMessage);
                }
            });
        });
        
        // Validate edit form
        $('#editReturnForm').submit(function(e) {
            e.preventDefault();
            
            $('#driverEditError, #editItemsError').text('');
            $('.edit-product-error, .edit-quantity-error').text('');
            
            var driverId = $('#selectedDriverEdit').val();
            if (!driverId) {
                $('#driverEditError').text('⚠️ Please select a driver');
                return false;
            }
            
            var hasErrors = false;
            var items = [];
            var productIds = new Set();
            
            $('#editItemsBody tr').each(function(index) {
                var productId = $(this).find('.edit-product-id-input').val();
                var quantity = $(this).find('.edit-quantity-input').val();
                var productError = $(this).find('.edit-product-error');
                var quantityError = $(this).find('.edit-quantity-error');
                
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
                
                if (!quantity || quantity < 1) {
                    quantityError.text('⚠️ Please enter a valid quantity (minimum 1)');
                    hasErrors = true;
                }
                
                if (productId && quantity && quantity >= 1) {
                    items.push({
                        product_id: parseInt(productId),
                        quantity: parseInt(quantity)
                    });
                }
            });
            
            if (items.length === 0) {
                $('#editItemsError').text('⚠️ Please add at least one valid item with product selected and quantity entered');
                hasErrors = true;
            }
            
            if (hasErrors) {
                $('#editItemsBody tr').each(function(index) {
                    var productId = $(this).find('.edit-product-id-input').val();
                    var quantity = $(this).find('.edit-quantity-input').val();
                    
                    if (!productId) {
                        $(this).find('.edit-product-dropdown').addClass('is-invalid');
                    } else {
                        $(this).find('.edit-product-dropdown').removeClass('is-invalid');
                    }
                    
                    if (!quantity || quantity < 1) {
                        $(this).find('.edit-quantity-input').addClass('is-invalid');
                    } else {
                        $(this).find('.edit-quantity-input').removeClass('is-invalid');
                    }
                });
                return false;
            }
            
            var formData = {
                driver_id: driverId,
                items: items,
                remarks: $('#remarksEdit').val(),
                _token: '{{ csrf_token() }}',
                _method: 'PUT'
            };
            
            if (typeof ShowLoad === 'function') ShowLoad();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (typeof HideLoad === 'function') HideLoad();
                    if (response.success) {
                        $('#editRequest').modal('hide');
                        showNotification('success', response.message);
                        
                        if (table && typeof table.ajax !== 'undefined') {
                            table.ajax.reload(null, false);
                        } else if (table && typeof table.draw !== 'undefined') {
                            table.draw(false);
                        }
                    } else {
                        showNotification('error', response.message || 'An error occurred');
                        if (response.errors) {
                            for (var field in response.errors) {
                                if (response.errors.hasOwnProperty(field)) {
                                    showNotification('error', response.errors[field][0]);
                                }
                            }
                        }
                    }
                },
                error: function(xhr) {
                    if (typeof HideLoad === 'function') HideLoad();
                    var errorMessage = 'An error occurred';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            for (var field in errors) {
                                if (errors.hasOwnProperty(field)) {
                                    errorMessage = errors[field][0];
                                    break;
                                }
                            }
                        }
                    }
                    
                    showNotification('error', errorMessage);
                }
            });
        });
        
        // ============================================
        // APPROVE/REJECT FUNCTIONS
        // ============================================
        
        $(document).on('click', '#approveFromViewBtn', function() {
            if (confirm('Are you sure you want to approve this inventory return?')) {
                approveRequest(currentRequestId);
            }
        });

        $(document).on('click', '#rejectFromViewBtn', function() {
            if (confirm('Are you sure you want to reject this inventory return?')) {
                rejectRequest(currentRequestId);
            }
        });
        
        function approveRequest(requestId) {
            if (typeof ShowLoad === 'function') ShowLoad();
            
            var url = '{{ route("inventoryReturns.approve", ":id") }}';
            url = url.replace(':id', requestId);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(response) {
                    if (typeof HideLoad === 'function') HideLoad();
                    if (response.success) {
                        showNotification('success', response.message);
                        $('#viewRequest').modal('hide');
                        
                        if (table && typeof table.ajax !== 'undefined') {
                            table.ajax.reload(null, false);
                        } else if (table && typeof table.draw !== 'undefined') {
                            table.draw(false);
                        }
                    } else {
                        showNotification('error', response.message || 'Failed to approve return');
                    }
                },
                error: function(xhr) {
                    if (typeof HideLoad === 'function') HideLoad();
                    showNotification('error', 'An error occurred while approving');
                }
            });
        }
        
        function rejectRequest(requestId) {
            if (typeof ShowLoad === 'function') ShowLoad();
            
            var url = '{{ route("inventoryReturns.reject", ":id") }}';
            url = url.replace(':id', requestId);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(response) {
                    if (typeof HideLoad === 'function') HideLoad();
                    if (response.success) {
                        showNotification('success', response.message);
                        $('#viewRequest').modal('hide');
                        
                        if (table && typeof table.ajax !== 'undefined') {
                            table.ajax.reload(null, false);
                        } else if (table && typeof table.draw !== 'undefined') {
                            table.draw(false);
                        }
                    } else {
                        showNotification('error', response.message || 'Failed to reject return');
                    }
                },
                error: function(xhr) {
                    if (typeof HideLoad === 'function') HideLoad();
                    showNotification('error', 'An error occurred while rejecting');
                }
            });
        }
        
        // ============================================
        // DELETE FUNCTIONALITY
        // ============================================
        
        $(document).on('submit', 'form[action*="destroy"]', function(e) {
            e.preventDefault();
            var form = $(this);
            
            if (confirm('Are you sure you want to delete this inventory return?')) {
                if (typeof ShowLoad === 'function') ShowLoad();
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-HTTP-Method-Override': 'DELETE'
                    },
                    success: function(response) {
                        if (typeof HideLoad === 'function') HideLoad();
                        if (response.success) {
                            showNotification('success', response.message);
                            
                            if (table && typeof table.ajax !== 'undefined') {
                                table.ajax.reload(null, false);
                            } else if (table && typeof table.draw !== 'undefined') {
                                table.draw(false);
                            } else {
                                location.reload();
                            }
                        } else {
                            showNotification('error', response.message || 'An error occurred');
                        }
                    },
                    error: function(xhr) {
                        if (typeof HideLoad === 'function') HideLoad();
                        showNotification('error', xhr.responseJSON?.message || 'An error occurred');
                    }
                });
            }
        });
        
        // ============================================
        // HELPER FUNCTIONS
        // ============================================
        
        function getStatusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'badge-pending';
                case 'approved': return 'badge-approved';
                case 'rejected': return 'badge-rejected';
                case 'cancelled': return 'badge-cancelled';
                default: return 'badge-info';
            }
        }

        function showNotification(type, message) {
            if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    text: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                alert(message);
            }
        }
        
        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            if (jqxhr.status === 419) {
                showNotification('error', 'Your session has expired. Please refresh the page.');
            } else if (jqxhr.status === 500) {
                showNotification('error', 'Server error occurred. Please try again.');
            }
        });
        
        // ============================================
        // CLEAR MODALS ON CLOSE
        // ============================================
        
        $('#editRequest').on('hidden.bs.modal', function () {
            $('#editReturnForm')[0].reset();
            $('#selectedDriverEdit').val('');
            $('#dropdownDriverEdit').html('<i class="fa fa-user mr-2"></i>Select Driver');
            // Clear all active classes from driver items
            $('#driverListEdit .driver-item').removeClass('active bg-primary text-white');
            $('#driverListEdit .driver-item').css('background', '');
            $('#editItemsBody').empty();
            editItemCounter = 0;
            $('#editItemsError, #driverEditError').text('');
        });

        $('#viewRequest').on('hidden.bs.modal', function () {
            $('#viewRequestId').text('');
            $('#viewRequestIdText, #viewDriverName, #viewRemarks, #viewCreatedAt, #viewApprovedBy, #viewApprovedAt').text('');
            $('#viewStatusBadge').html('');
            $('#viewActionButtons').html('');
            $('#viewItemsTable').html('');
            currentRequestId = null;
            currentRequestStatus = null;
        });
    });

    $(document).keyup(function(e) {
        if(e.altKey && e.keyCode == 78 && ($('#createRequest').length > 0)) {
            $('#createRequest').modal('show');
        }
    });
</script>
@endpush