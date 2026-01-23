@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">{{ __('Stock Requests') }}</li>
    </ol>
    <div class="container-fluid">
        <div class="animated fadeIn">
            @include('flash::message')
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-align-justify"></i>
                            {{ __('Stock Requests') }}
                        </div>
                        <div class="card-body">
                            @include('inventory_requests.table')
                            <div class="pull-right mr-3">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Request Modal -->
    <div id="createRequest" class="modal fade">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ __('Create Stock Request') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => 'inventoryRequests.store', 'enctype' => 'multipart/form-data', 'id' => 'createRequestForm']) !!}
                    
                    <div class="row">
                        <!-- Driver Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver_id" class="col-form-label">{{ __('Driver') }} <span class="text-danger">*</span>:</label>
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary btn-block dropdown-toggle" type="button" id="dropdownDriverCreate" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('Select Driver') }}
                                    </button>
                                    <div class="dropdown-menu p-3" aria-labelledby="dropdownDriverCreate" style="width: 100%; max-height: 300px; overflow-y: auto;">
                                        <input type="text" class="form-control mb-3" id="driverSearchCreate" placeholder="Search Drivers...">
                                        <div id="driverListCreate" class="list-group">
                                            @foreach($drivers as $driver)
                                                <a href="#" class="list-group-item list-group-item-action driver-item" data-value="{{ $driver->id }}">
                                                    {{ $driver->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="driver_id" id="selectedDriverCreate" required>
                                <div class="text-danger" id="driverError"></div>
                            </div>
                        </div>
                        
                        <!-- Remarks -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="remarks" class="col-form-label">{{ __('Remarks') }} ({{ __('Optional') }}):</label>
                                <textarea class="form-control" name="remarks" id="remarks" rows="2" placeholder="Any additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Table -->
                    <div class="form-group">
                        <label class="col-form-label">{{ __('Items') }} <span class="text-danger">*</span>:</label>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="45%">Product <span class="text-danger">*</span></th>
                                        <th width="20%">Quantity <span class="text-danger">*</span></th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <!-- Items will be added here dynamically -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-right">
                                            <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                                                <i class="fa fa-plus"></i> Add Item
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="text-danger" id="itemsError"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-0">{{ __('Submit Request') }}</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Request Modal -->
    <div id="editRequest" class="modal fade">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ __('Edit Stock Request') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => ['inventoryRequests.update', ':id'], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'id' => 'editRequestForm']) !!}
                    
                    <!-- Add a hidden field to track if we want to save and approve -->
                    <input type="hidden" name="save_and_approve" id="saveAndApprove" value="0">
                    
                    <div class="row">
                        <!-- Driver Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver_id" class="col-form-label">{{ __('Driver') }} <span class="text-danger">*</span>:</label>
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary btn-block dropdown-toggle" type="button" id="dropdownDriverEdit" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('Select Driver') }}
                                    </button>
                                    <div class="dropdown-menu p-3" aria-labelledby="dropdownDriverEdit" style="width: 100%; max-height: 300px; overflow-y: auto;">
                                        <input type="text" class="form-control mb-3" id="driverSearchEdit" placeholder="Search Drivers...">
                                        <div id="driverListEdit" class="list-group">
                                            @foreach($drivers as $driver)
                                                <a href="#" class="list-group-item list-group-item-action driver-item" data-value="{{ $driver->id }}">
                                                    {{ $driver->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="driver_id" id="selectedDriverEdit" required>
                                <div class="text-danger" id="driverEditError"></div>
                            </div>
                        </div>
                        
                        <!-- Remarks -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="remarks" class="col-form-label">{{ __('Remarks') }} ({{ __('Optional') }}):</label>
                                <textarea class="form-control" name="remarks" id="remarksEdit" rows="2" placeholder="Any additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Table for Edit -->
                    <div class="form-group">
                        <label class="col-form-label">{{ __('Items') }} <span class="text-danger">*</span>:</label>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="editItemsTable">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="45%">Product <span class="text-danger">*</span></th>
                                        <th width="20%">Quantity <span class="text-danger">*</span></th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="editItemsBody">
                                    <!-- Items will be populated here -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-right">
                                            <button type="button" class="btn btn-success btn-sm" id="addEditItemBtn">
                                                <i class="fa fa-plus"></i> Add Item
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="text-danger" id="editItemsError"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-0">{{ __('Update Request') }}</button>
                        
                        <!-- Add Save & Approve button for admins -->
                        @if(auth()->user()->hasRole('admin'))
                            <button type="button" class="btn btn-success rounded-0" id="saveAndApproveBtn">
                                {{ __('Save & Approve') }}
                            </button>
                        @endif
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Request Modal -->
    <div id="viewRequest" class="modal fade">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">Stock Request Details <span id="viewRequestId"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th width="30%">Request ID:</th>
                                        <td id="viewRequestIdText"></td>
                                    </tr>
                                    <tr>
                                        <th>Driver:</th>
                                        <td id="viewDriverName"></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td><span id="viewStatusBadge"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Remarks:</th>
                                        <td id="viewRemarks"></td>
                                    </tr>
                                    <tr>
                                        <th>Requested At:</th>
                                        <td id="viewCreatedAt"></td>
                                    </tr>
                                    <tr id="viewApprovedSection" style="display: none;">
                                        <th>Approved By:</th>
                                        <td id="viewApprovedBy"></td>
                                    </tr>
                                    <tr id="viewApprovedAtSection" style="display: none;">
                                        <th>Approved At:</th>
                                        <td id="viewApprovedAt"></td>
                                    </tr>
                                    <tr id="viewRejectedSection" style="display: none;">
                                        <th>Rejected By:</th>
                                        <td id="viewRejectedBy"></td>
                                    </tr>
                                    <tr id="viewRejectedAtSection" style="display: none;">
                                        <th>Rejected At:</th>
                                        <td id="viewRejectedAt"></td>
                                    </tr>
                                    <tr id="viewRejectionReasonSection" style="display: none;">
                                        <th>Rejection Reason:</th>
                                        <td id="viewRejectionReason"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Items Table Section -->
                    <div class="mt-4">
                        <h5>Requested Items</h5>
                        <div id="viewItemsTable"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100">
                        <div id="viewActionButtons">
                            <!-- Action buttons will be shown here -->
                        </div>
                        <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Reason Modal (for reject action) -->
    <div id="rejectReasonModal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Request</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rejectRequestId" value="">
                    <div class="form-group">
                        <label for="rejection_reason_modal">Rejection Reason *</label>
                        <textarea name="rejection_reason" id="rejection_reason_modal" class="form-control" rows="3" required placeholder="Please provide a reason for rejection"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectBtn">Confirm Reject</button>
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
        .btn-outline-secondary {
            border-color: #6c757d;
            color: #6c757d;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }
        .product-item:hover, 
        .product-item.active,
        .driver-item:hover,
        .driver-item.active,
        .product-select-item:hover,
        .product-select-item.active {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        .list-group-item {
            border: 1px solid rgba(0,0,0,.125);
            margin-bottom: -1px;
        }
        .badge {
            font-size: 85%;
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
        .item-row td {
            vertical-align: middle !important;
        }
    </style>

<script>
        $(document).ready(function () {
        // Initialize DataTable
        var table = window.LaravelDataTables["dataTableBuilder"] || $('.data-table').DataTable();
        
        if (table) {
            // Hide loading when DataTable initializes
            $(document).on('init.dt', function (e, settings) {
                if (e.namespace === 'dt') {
                    setTimeout(function() {
                        HideLoad();
                    }, 100);
                }
            });
            
            // Also hide loading on draw (for filters, pagination, etc.)
            table.on('draw', function () {
                setTimeout(function() {
                    HideLoad();
                }, 100);
            });
            
            // Force hide loading after DataTable is initialized
            setTimeout(function() {
                HideLoad();
            }, 1000);
        }
        
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
        
        // ============================================
        // CREATE MODAL FUNCTIONS
        // ============================================
        
        // Initialize create modal items table
        function initializeItemsTable() {
            $('#itemsBody').empty();
            itemCounter = 0;
            addItemRow();
        }
        
        // Add item row to create modal
        function addItemRow() {
            var row = `
                <tr class="item-row" data-index="${itemCounter}">
                    <td class="align-middle text-center">${itemCounter + 1}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-block dropdown-toggle text-left product-dropdown" type="button" id="productDropdown${itemCounter}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="overflow: hidden; text-overflow: ellipsis;">
                                {{ __('Select Product') }}
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="productDropdown${itemCounter}" style="width: 100%; max-height: 300px; overflow-y: auto;">
                                <input type="text" class="form-control mb-3 product-search" placeholder="Search Products..." data-index="${itemCounter}">
                                <div class="product-list" data-index="${itemCounter}">
                                    @foreach($products as $product)
                                        <a href="#" class="list-group-item list-group-item-action product-select-item" data-index="${itemCounter}" data-value="{{ $product->id }}" data-name="{{ $product->name }}">
                                            {{ $product->name }} ({{ $product->code }})
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="product-id-input" name="items[${itemCounter}][product_id]" value="">
                        <div class="text-danger product-error small"></div>
                    </td>
                    <td>
                        <input type="number" min="1" class="form-control quantity-input" name="items[${itemCounter}][quantity]" placeholder="Enter quantity">
                        <div class="text-danger quantity-error small"></div>
                    </td>
                    <td class="align-middle text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-item-btn" ${itemCounter === 0 ? 'disabled' : ''}>
                            <i class="fa fa-trash"></i>
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
        
        // Driver selection for create modal
        $(document).on('click', '#driverListCreate .driver-item', function(e) {
            e.preventDefault();
            var driverName = $(this).text();
            var driverId = $(this).data('value');
            
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            $('#dropdownDriverCreate').text(driverName);
            $('#selectedDriverCreate').val(driverId);
            $('#driverError').text('');
        });
        
        // Product search in create modal
        $(document).on('keyup', '.product-search', function() {
            var searchTerm = $(this).val().toLowerCase();
            var index = $(this).data('index');
            var productList = $(this).siblings('.product-list[data-index="' + index + '"]');
            
            productList.find('.product-select-item').each(function() {
                var productText = $(this).text().toLowerCase();
                if (productText.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Product selection in create modal
        $(document).on('click', '.product-select-item', function(e) {
            e.preventDefault();
            var index = $(this).data('index');
            var productId = $(this).data('value');
            var productName = $(this).data('name');
            
            // Update the dropdown button
            $('#productDropdown' + index).text(productName).attr('title', productName);
            
            // Set the hidden input value
            $(this).closest('tr').find('.product-id-input').val(productId);
            
            // Clear error
            $(this).closest('tr').find('.product-error').text('');
            
            // Highlight selected item
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
        });
        
        // Add item button
        $('#addItemBtn').on('click', function() {
            addItemRow();
        });
        
        // Remove item button
        $(document).on('click', '.remove-item-btn', function() {
            if ($('#itemsBody tr').length > 1) {
                var row = $(this).closest('tr');
                var rowIndex = parseInt(row.data('index'));
                
                row.remove();
                
                // Renumber rows and update indices
                $('#itemsBody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                    $(this).attr('data-index', index);
                    
                    // Update dropdown IDs and attributes
                    var dropdownBtn = $(this).find('.product-dropdown');
                    var dropdownId = 'productDropdown' + index;
                    dropdownBtn.attr('id', dropdownId)
                               .attr('aria-labelledby', dropdownId);
                    
                    // Update search and list indices
                    $(this).find('.product-search, .product-list').attr('data-index', index);
                    $(this).find('.product-select-item').attr('data-index', index);
                    
                    // Update input names
                    $(this).find('.product-id-input, .quantity-input').each(function() {
                        var name = $(this).attr('name');
                        if (name && name.includes('items[')) {
                            var newName = name.replace(/items\[\d+\]/, 'items[' + index + ']');
                            $(this).attr('name', newName);
                        }
                    });
                });
                
                // Update counter
                itemCounter = $('#itemsBody tr').length;
                
                // Disable remove button on first row if only one row left
                if ($('#itemsBody tr').length === 1) {
                    $('#itemsBody tr:first .remove-item-btn').prop('disabled', true);
                }
            }
        });
        
        // Clear create modal when opened
        $('#createRequest').on('show.bs.modal', function () {
            initializeItemsTable();
            $('#createRequestForm')[0].reset();
            $('#selectedDriverCreate').val('');
            $('#dropdownDriverCreate').text('{{ __('Select Driver') }}');
            $('#driverListCreate .driver-item').removeClass('active');
            $('#driverError, #itemsError').text('');
            $('#remarks').val('');
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
                    <td class="align-middle text-center">${editItemCounter + 1}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-block dropdown-toggle text-left edit-product-dropdown" type="button" id="editProductDropdown${editItemCounter}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="overflow: hidden; text-overflow: ellipsis;">
                                ${displayName}
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="editProductDropdown${editItemCounter}" style="width: 100%; max-height: 300px; overflow-y: auto;">
                                <input type="text" class="form-control mb-3 edit-product-search" placeholder="Search Products..." data-index="${editItemCounter}">
                                <div class="edit-product-list" data-index="${editItemCounter}">
                                    @foreach($products as $product)
                                        <a href="#" class="list-group-item list-group-item-action edit-product-select-item" data-index="${editItemCounter}" data-value="{{ $product->id }}" data-name="{{ $product->name }}">
                                            {{ $product->name }} ({{ $product->code }})
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="edit-product-id-input" name="items[${editItemCounter}][product_id]" value="${productId}">
                        <div class="text-danger edit-product-error small"></div>
                    </td>
                    <td>
                        <input type="number" min="1" class="form-control edit-quantity-input" name="items[${editItemCounter}][quantity]" placeholder="Enter quantity" value="${quantity}">
                        <div class="text-danger edit-quantity-error small"></div>
                    </td>
                    <td class="align-middle text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-edit-item-btn" ${editItemCounter === 0 ? 'disabled' : ''}>
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            $('#editItemsBody').append(row);
            editItemCounter++;
            
            // Enable remove buttons if more than one row
            if ($('#editItemsBody tr').length > 1) {
                $('#editItemsBody tr:first .remove-edit-item-btn').prop('disabled', false);
            }
        }
        
        // Handle edit modal opening
        $(document).on('click', '.edit-request-btn', function(e) {
            e.preventDefault();
            var requestId = $(this).data('id');
            var requestData = $(this).data('request');
            
            // Parse JSON string if needed
            if (typeof requestData === 'string') {
                requestData = JSON.parse(requestData);
            }
            
            // Store current request ID
            currentRequestId = requestId;
            
            // Update form action
            var formAction = '{{ route("inventoryRequests.update", ":id") }}';
            formAction = formAction.replace(':id', requestId);
            $('#editRequestForm').attr('action', formAction);
            
            // Fill basic info
            $('#selectedDriverEdit').val(requestData.driver_id);
            $('#dropdownDriverEdit').text(requestData.driver_name || 'Select Driver');
            $('#remarksEdit').val(requestData.remarks || '');
            
            // Highlight selected driver
            $('#driverListEdit .driver-item').removeClass('active');
            $('#driverListEdit .driver-item[data-value="' + requestData.driver_id + '"]').addClass('active');
            
            // Clear and populate items table
            initializeEditItemsTable();
            
            if (requestData.items && Array.isArray(requestData.items) && requestData.items.length > 0) {
                $('#editItemsBody').empty();
                editItemCounter = 0;
                
                requestData.items.forEach(function(item, index) {
                    var productName = getProductName(item.product_id);
                    addEditItemRow(item.product_id, productName, item.quantity);
                });
            } else {
                // For backward compatibility with old single-item requests
                if (requestData.product_id && requestData.quantity) {
                    $('#editItemsBody').empty();
                    editItemCounter = 0;
                    var productName = getProductName(requestData.product_id);
                    addEditItemRow(requestData.product_id, productName, requestData.quantity);
                }
            }
            
            // Show modal
            $('#editRequest').modal('show');
        });
        
        // Driver selection for edit modal
        $(document).on('click', '#driverListEdit .driver-item', function(e) {
            e.preventDefault();
            var driverName = $(this).text();
            var driverId = $(this).data('value');
            
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            $('#dropdownDriverEdit').text(driverName);
            $('#selectedDriverEdit').val(driverId);
            $('#driverEditError').text('');
        });
        
        // Product search in edit modal
        $(document).on('keyup', '.edit-product-search', function() {
            var searchTerm = $(this).val().toLowerCase();
            var index = $(this).data('index');
            var productList = $(this).siblings('.edit-product-list[data-index="' + index + '"]');
            
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
            
            // Update the dropdown button
            $('#editProductDropdown' + index).text(productName).attr('title', productName);
            
            // Set the hidden input value
            $(this).closest('tr').find('.edit-product-id-input').val(productId);
            
            // Clear error
            $(this).closest('tr').find('.edit-product-error').text('');
            
            // Highlight selected item
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
        });
        
        // Add item button for edit modal
        $('#addEditItemBtn').on('click', function() {
            addEditItemRow();
        });
        
        // Remove item button for edit modal
        $(document).on('click', '.remove-edit-item-btn', function() {
            if ($('#editItemsBody tr').length > 1) {
                var row = $(this).closest('tr');
                var rowIndex = parseInt(row.data('index'));
                
                row.remove();
                
                // Renumber rows and update indices
                $('#editItemsBody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                    $(this).attr('data-index', index);
                    
                    // Update dropdown IDs and attributes
                    var dropdownBtn = $(this).find('.edit-product-dropdown');
                    var dropdownId = 'editProductDropdown' + index;
                    dropdownBtn.attr('id', dropdownId)
                               .attr('aria-labelledby', dropdownId);
                    
                    // Update search and list indices
                    $(this).find('.edit-product-search, .edit-product-list').attr('data-index', index);
                    $(this).find('.edit-product-select-item').attr('data-index', index);
                    
                    // Update input names
                    $(this).find('.edit-product-id-input, .edit-quantity-input').each(function() {
                        var name = $(this).attr('name');
                        if (name && name.includes('items[')) {
                            var newName = name.replace(/items\[\d+\]/, 'items[' + index + ']');
                            $(this).attr('name', newName);
                        }
                    });
                });
                
                // Update counter
                editItemCounter = $('#editItemsBody tr').length;
                
                // Disable remove button on first row if only one row left
                if ($('#editItemsBody tr').length === 1) {
                    $('#editItemsBody tr:first .remove-edit-item-btn').prop('disabled', true);
                }
            }
        });
        
        // ============================================
        // SAVE AND APPROVE FUNCTIONALITY
        // ============================================
        
        // Handle Save & Approve button click
        $(document).on('click', '#saveAndApproveBtn', function() {
            if (confirm('Are you sure you want to save changes and approve this request? This will immediately add the items to driver inventory.')) {
                // Set the hidden field to indicate we want to save and approve
                $('#saveAndApprove').val('1');
                
                // Submit the form
                $('#editRequestForm').submit();
            }
        });
        
        // Reset the save_and_approve flag when form is submitted normally
        $('#editRequestForm').submit(function(e) {
            // If not triggered by saveAndApproveBtn, ensure flag is 0
            if ($('#saveAndApprove').val() !== '1') {
                $('#saveAndApprove').val('0');
            }
        });
        
        // Reset the flag when modal is closed
        $('#editRequest').on('hidden.bs.modal', function () {
            $('#saveAndApprove').val('0');
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
        $(document).on('click', '.view-request-btn', function(e) {
            e.preventDefault();
            var requestId = $(this).data('id');
            var requestData = $(this).data('request');
            
            // Parse JSON string if needed
            if (typeof requestData === 'string') {
                requestData = JSON.parse(requestData);
            }
            
            // Store current request info
            currentRequestId = requestId;
            currentRequestStatus = requestData.status;
            
            // Update modal title with request ID
            $('#viewRequestId').text('(#' + requestId + ')');
            $('#viewRequestIdText').text(requestId);
            
            // Fill view modal with data
            $('#viewDriverName').text(requestData.driver_name || 'N/A');
            $('#viewRemarks').text(requestData.remarks || 'No remarks');
            $('#viewCreatedAt').text(requestData.created_at || 'N/A');
            
            // Set status with badge
            var status = requestData.status;
            var badgeClass = getStatusBadgeClass(status);
            
            $('#viewStatusBadge').html('<span class="badge ' + badgeClass + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>');
            
            // Show items table
            if (requestData.items && Array.isArray(requestData.items) && requestData.items.length > 0) {
                var itemsHtml = '<div class="table-responsive"><table class="table table-sm table-bordered">';
                itemsHtml += '<thead><tr><th>#</th><th>Product</th><th>Requested Quantity</th></tr></thead><tbody>';
                
                var totalQuantity = 0;
                
                requestData.items.forEach(function(item, index) {
                    var quantity = item.quantity || 0;
                    var productName = item.product_name;
                    if (!productName && item.product_id) {
                        productName = getProductName(item.product_id);
                    } else if (!productName) {
                        productName = 'Unknown Product';
                    }
                    
                    itemsHtml += '<tr>';
                    itemsHtml += '<td>' + (index + 1) + '</td>';
                    itemsHtml += '<td>' + productName + '</td>'; 
                    itemsHtml += '<td class="text-center">' + quantity + '</td>';
                    itemsHtml += '</tr>';
                    
                    totalQuantity += parseInt(quantity);
                });
                
                itemsHtml += '</tbody>';
                itemsHtml += '<tfoot><tr>';
                itemsHtml += '<td colspan="2" class="text-right"><strong>Total:</strong></td>';
                itemsHtml += '<td class="text-center"><strong>' + totalQuantity + '</strong></td>';
                itemsHtml += '</tr></tfoot>';
                itemsHtml += '</table></div>';
                
                $('#viewItemsTable').html(itemsHtml);
            } else {
                // For backward compatibility with old single-item requests
                var singleItemHtml = '<div class="table-responsive"><table class="table table-sm table-bordered">';
                singleItemHtml += '<thead><tr><th>#</th><th>Product</th><th>Requested Quantity</th></tr></thead><tbody>';
                
                if (requestData.product_id && requestData.quantity) {
                    var productName = getProductName(requestData.product_id);
                    singleItemHtml += '<tr>';
                    singleItemHtml += '<td>1</td>';
                    singleItemHtml += '<td>' + (requestData.product_name || productName) + '</td>'; 
                    singleItemHtml += '<td class="text-center">' + requestData.quantity + '</td>';
                    singleItemHtml += '</tr>';
                }
                
                singleItemHtml += '</tbody></table></div>';
                $('#viewItemsTable').html(singleItemHtml);
            }
            
            // Show/hide approval section - ONLY show when status is approved
            if (status === 'approved') {
                $('#viewApprovedSection').show();
                $('#viewApprovedAtSection').show();
                $('#viewApprovedBy').text(requestData.approved_by || 'N/A');
                $('#viewApprovedAt').text(requestData.approved_at || 'N/A');
            } else {
                $('#viewApprovedSection').hide();
                $('#viewApprovedAtSection').hide();
            }
            
            // Show/hide rejection section - ONLY show when status is rejected
            if (status === 'rejected') {
                $('#viewRejectedSection').show();
                $('#viewRejectedAtSection').show();
                $('#viewRejectionReasonSection').show();
                $('#viewRejectedBy').text(requestData.rejected_by || 'N/A');
                $('#viewRejectedAt').text(requestData.rejected_at || 'N/A');
                $('#viewRejectionReason').text(requestData.rejection_reason || 'No reason provided');
            } else {
                $('#viewRejectedSection').hide();
                $('#viewRejectedAtSection').hide();
                $('#viewRejectionReasonSection').hide();
            }
            
            // Show/hide action buttons based on status and permissions
            var actionButtonsHtml = '';
            if (status === 'pending') {
                actionButtonsHtml = `
                    <button type="button" class="btn btn-success mr-2" id="approveFromViewBtn">
                        <i class="fa fa-check"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger" id="rejectFromViewBtn">
                        <i class="fa fa-times"></i> Reject
                    </button>
                `;
            }
            $('#viewActionButtons').html(actionButtonsHtml);
            
            // Show modal
            $('#viewRequest').modal('show');
        });
        
        // ============================================
        // FORM VALIDATION AND SUBMISSION
        // ============================================
        
        // Validate create form
        $('#createRequestForm').submit(function(e) {
            e.preventDefault();
            
            // Reset errors
            $('#driverError, #itemsError').text('');
            $('.product-error, .quantity-error').text('');
            
            // Validate driver
            var driverId = $('#selectedDriverCreate').val();
            if (!driverId) {
                $('#driverError').text('Please select a driver');
                return false;
            }
            
            // Validate items
            var hasErrors = false;
            var items = [];
            var productIds = new Set(); // To check for duplicate products
            
            $('#itemsBody tr').each(function(index) {
                var productId = $(this).find('.product-id-input').val();
                var quantity = $(this).find('.quantity-input').val();
                var productError = $(this).find('.product-error');
                var quantityError = $(this).find('.quantity-error');
                
                // Reset errors
                productError.text('');
                quantityError.text('');
                
                // Validate product
                if (!productId) {
                    productError.text('Please select a product');
                    hasErrors = true;
                } else if (productIds.has(productId)) {
                    productError.text('Duplicate product selected');
                    hasErrors = true;
                } else {
                    productIds.add(productId);
                }
                
                // Validate quantity
                if (!quantity || quantity < 1) {
                    quantityError.text('Please enter a valid quantity (minimum 1)');
                    hasErrors = true;
                }
                
                // Add to items array only if valid
                if (productId && quantity && quantity >= 1) {
                    items.push({
                        product_id: parseInt(productId),
                        quantity: parseInt(quantity)
                    });
                }
            });
            
            if (items.length === 0) {
                $('#itemsError').text('Please add at least one valid item with product selected and quantity entered');
                hasErrors = true;
            }
            
            if (hasErrors) {
                // Highlight problematic rows
                $('#itemsBody tr').each(function(index) {
                    var productId = $(this).find('.product-id-input').val();
                    var quantity = $(this).find('.quantity-input').val();
                    
                    if (!productId) {
                        $(this).find('.product-dropdown').addClass('is-invalid');
                    } else {
                        $(this).find('.product-dropdown').removeClass('is-invalid');
                    }
                    
                    if (!quantity || quantity < 1) {
                        $(this).find('.quantity-input').addClass('is-invalid');
                    } else {
                        $(this).find('.quantity-input').removeClass('is-invalid');
                    }
                });
                return false;
            }
            
            // Prepare all form data including items
            var formData = {
                driver_id: driverId,
                items: items,
                remarks: $('#remarks').val(),
                _token: '{{ csrf_token() }}'
            };
            
            // Submit via AJAX
            ShowLoad();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    HideLoad();
                    if (response.success) {
                        $('#createRequest').modal('hide');
                        showNotification('success', response.message);
                        
                        // Refresh DataTable
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
                    HideLoad();
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
        $('#editRequestForm').submit(function(e) {
            e.preventDefault();
            
            // Reset errors
            $('#driverEditError, #editItemsError').text('');
            $('.edit-product-error, .edit-quantity-error').text('');
            
            // Validate driver
            var driverId = $('#selectedDriverEdit').val();
            if (!driverId) {
                $('#driverEditError').text('Please select a driver');
                return false;
            }
            
            // Validate items
            var hasErrors = false;
            var items = [];
            var productIds = new Set(); // To check for duplicate products
            
            $('#editItemsBody tr').each(function(index) {
                var productId = $(this).find('.edit-product-id-input').val();
                var quantity = $(this).find('.edit-quantity-input').val();
                var productError = $(this).find('.edit-product-error');
                var quantityError = $(this).find('.edit-quantity-error');
                
                // Reset errors
                productError.text('');
                quantityError.text('');
                
                // Validate product
                if (!productId) {
                    productError.text('Please select a product');
                    hasErrors = true;
                } else if (productIds.has(productId)) {
                    productError.text('Duplicate product selected');
                    hasErrors = true;
                } else {
                    productIds.add(productId);
                }
                
                // Validate quantity
                if (!quantity || quantity < 1) {
                    quantityError.text('Please enter a valid quantity (minimum 1)');
                    hasErrors = true;
                }
                
                // Add to items array only if valid
                if (productId && quantity && quantity >= 1) {
                    items.push({
                        product_id: parseInt(productId),
                        quantity: parseInt(quantity)
                    });
                }
            });
            
            if (items.length === 0) {
                $('#editItemsError').text('Please add at least one valid item with product selected and quantity entered');
                hasErrors = true;
            }
            
            if (hasErrors) {
                // Highlight problematic rows
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
            
            // Prepare all form data including items
            var formData = {
                driver_id: driverId,
                items: items,
                remarks: $('#remarksEdit').val(),
                save_and_approve: $('#saveAndApprove').val(), // Include the flag
                _token: '{{ csrf_token() }}',
                _method: 'PUT'
            };
            
            // Submit via AJAX
            ShowLoad();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    HideLoad();
                    if (response.success) {
                        $('#editRequest').modal('hide');
                        showNotification('success', response.message);
                        
                        // Reset the flag
                        $('#saveAndApprove').val('0');
                        
                        // Refresh DataTable
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
                    HideLoad();
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
        
        // Handle approve action from view modal
        $(document).on('click', '#approveFromViewBtn', function() {
            if (confirm('Are you sure you want to approve this inventory request?')) {
                approveRequest(currentRequestId);
            }
        });

        // Handle reject action from view modal
        $(document).on('click', '#rejectFromViewBtn', function() {
            $('#rejectRequestId').val(currentRequestId);
            $('#rejection_reason_modal').val('');
            $('#rejectReasonModal').modal('show');
        });

        // Handle confirm reject from reject modal
        $('#confirmRejectBtn').on('click', function() {
            var rejectReason = $('#rejection_reason_modal').val();
            if (!rejectReason.trim()) {
                alert('Please provide a rejection reason');
                return;
            }
            
            rejectRequest($('#rejectRequestId').val(), rejectReason);
        });
        
        // ============================================
        // DELETE FUNCTIONALITY
        // ============================================
        
        // Handle delete action via AJAX
        $(document).on('submit', 'form[action*="destroy"]', function(e) {
            e.preventDefault();
            var form = $(this);
            
            if (confirm('Are you sure you want to delete this inventory request?')) {
                ShowLoad();
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-HTTP-Method-Override': 'DELETE'
                    },
                    success: function(response) {
                        HideLoad();
                        if (response.success) {
                            showNotification('success', response.message);
                            
                            // Refresh the DataTable
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
                        HideLoad();
                        showNotification('error', xhr.responseJSON?.message || 'An error occurred');
                    }
                });
            }
        });
        
        // ============================================
        // HELPER FUNCTIONS
        // ============================================
        
        // Helper function to approve request
        function approveRequest(requestId) {
            ShowLoad();
            
            var url = '{{ route("inventoryRequests.approve", ":id") }}';
            url = url.replace(':id', requestId);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(response) {
                    HideLoad();
                    if (response.success) {
                        showNotification('success', response.message);
                        
                        // Close all modals
                        $('#viewRequest, #rejectReasonModal').modal('hide');
                        
                        // Refresh the DataTable
                        if (table && typeof table.ajax !== 'undefined') {
                            table.ajax.reload(null, false);
                        } else if (table && typeof table.draw !== 'undefined') {
                            table.draw(false);
                        } else {
                            location.reload();
                        }
                    } else {
                        showNotification('error', response.message || 'Failed to approve request');
                    }
                },
                error: function(xhr) {
                    HideLoad();
                    var errorMessage = 'An error occurred while approving';
                    
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
        }

        // Helper function to reject request
        function rejectRequest(requestId, rejectReason, modal = null) {
            ShowLoad();
            
            var url = '{{ route("inventoryRequests.reject", ":id") }}';
            url = url.replace(':id', requestId);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    rejection_reason: rejectReason
                },
                dataType: 'json',
                success: function(response) {
                    HideLoad();
                    if (response.success) {
                        showNotification('success', response.message);
                        
                        // Close all modals
                        if (modal) modal.modal('hide');
                        $('#viewRequest, #rejectReasonModal').modal('hide');
                        
                        // Refresh the DataTable
                        if (table && typeof table.ajax !== 'undefined') {
                            table.ajax.reload(null, false);
                        } else if (table && typeof table.draw !== 'undefined') {
                            table.draw(false);
                        } else {
                            location.reload();
                        }
                    } else {
                        showNotification('error', response.message || 'Failed to reject request');
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
                    HideLoad();
                    var errorMessage = 'An error occurred while rejecting';
                    
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
        }

        // Helper function for status badge class
        function getStatusBadgeClass(status) {
            switch (status) {
                case 'pending': return 'badge-pending';
                case 'approved': return 'badge-approved';
                case 'rejected': return 'badge-rejected';
                case 'cancelled': return 'badge-cancelled';
                default: return 'badge-info';
            }
        }

        // Helper function for notifications
        function showNotification(type, message) {
            // Check if toastr is available
            if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } 
            // Check if Swal (SweetAlert) is available
            else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    text: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
            // Fallback to regular alert
            else {
                alert(message);
            }
        }
        
        // Optional: Add better error handling for AJAX requests
        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            if (jqxhr.status === 419) { // CSRF token mismatch
                showNotification('error', 'Your session has expired. Please refresh the page.');
            } else if (jqxhr.status === 500) {
                showNotification('error', 'Server error occurred. Please try again.');
            }
        });
        
        // ============================================
        // CLEAR MODALS ON CLOSE
        // ============================================
        
        // Clear edit modal when closed
        $('#editRequest').on('hidden.bs.modal', function () {
            $('#editRequestForm')[0].reset();
            $('#selectedDriverEdit').val('');
            $('#dropdownDriverEdit').text('Select Driver');
            $('#driverListEdit .driver-item').removeClass('active');
            $('#editItemsBody').empty();
            editItemCounter = 0;
            $('#editItemsError, #driverEditError').text('');
        });

        // Clear view modal when closed
        $('#viewRequest').on('hidden.bs.modal', function () {
            // Reset view modal fields
            $('#viewRequestId').text('');
            $('#viewRequestIdText, #viewDriverName, #viewRemarks, #viewCreatedAt, #viewApprovedBy, #viewApprovedAt, #viewRejectedBy, #viewRejectedAt, #viewRejectionReason').text('');
            $('#viewStatusBadge').html('');
            $('#viewActionButtons').html('');
            $('#viewItemsTable').html('');
            currentRequestId = null;
            currentRequestStatus = null;
        });

        // Clear reject modal when closed
        $('#rejectReasonModal').on('hidden.bs.modal', function () {
            $('#rejectRequestId').val('');
            $('#rejection_reason_modal').val('');
        });
    });

    // Keyboard shortcut for creating new request
    $(document).keyup(function(e) {
        if(e.altKey && e.keyCode == 78 && ($('#createRequest').length > 0)) {
            $('#createRequest').modal('show');
        }
    });
</script>
@endpush