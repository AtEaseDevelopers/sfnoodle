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
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ __('Create Stock Request') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body text-center">
                    {!! Form::open(['route' => 'inventoryRequests.store', 'enctype' => 'multipart/form-data', 'id' => 'createRequestForm']) !!}
                                        
                        <div class="form-group">
                            <label for="driver_id" class="col-form-label">{{ __('Driver') }}:</label>
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
                            <input type="hidden" name="driver_id" id="selectedDriverCreate">
                            @error('driver_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                    <!-- Product Single-Select Dropdown -->
                    <div class="form-group">
                        <label for="product_id" class="col-form-label">{{ __('Product') }}:</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-block dropdown-toggle" type="button" id="dropdownProductCreate" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ __('Select Product') }}
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="dropdownProductCreate" style="width: 100%; max-height: 300px; overflow-y: auto;">
                                <input type="text" class="form-control mb-3" id="productSearchCreate" placeholder="Search Products...">
                                <div id="productListCreate" class="list-group">
                                    @foreach($products as $product)
                                        <a href="#" class="list-group-item list-group-item-action product-item" data-value="{{ $product->id }}">
                                            {{ $product->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="product_id" id="selectedProductCreate">
                        @error('product_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Quantity Input -->
                    <div class="form-group">
                        <label for="quantity" class="col-form-label">{{ __('Quantity') }}:</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-cubes"></i></span>
                            </div>
                            <input type="number" min="1" class="form-control" placeholder="Enter quantity" name="quantity" id="quantityCreate" required>
                        </div>
                        @error('quantity')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Notes/Remarks (Optional) -->
                    <div class="form-group">
                        <label for="remarks" class="col-form-label">{{ __('Remarks') }} ({{ __('Optional') }}):</label>
                        <textarea class="form-control" name="remarks" id="remarks" rows="2" placeholder="Any additional notes..."></textarea>
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
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ __('Edit Stock Request') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body text-center">
                    {!! Form::open(['route' => ['inventoryRequests.update', ':id'], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'id' => 'editRequestForm']) !!}
                    
                    <!-- Driver Selection -->
                    <div class="form-group">
                        <label for="driver_id" class="col-form-label">{{ __('Driver') }}:</label>
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
                        <input type="hidden" name="driver_id" id="selectedDriverEdit">
                        @error('driver_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Product Single-Select Dropdown -->
                    <div class="form-group">
                        <label for="product_id" class="col-form-label">{{ __('Product') }}:</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-block dropdown-toggle" type="button" id="dropdownProductEdit" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ __('Select Product') }}
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="dropdownProductEdit" style="width: 100%; max-height: 300px; overflow-y: auto;">
                                <input type="text" class="form-control mb-3" id="productSearchEdit" placeholder="Search Products...">
                                <div id="productListEdit" class="list-group">
                                    @foreach($products as $product)
                                        <a href="#" class="list-group-item list-group-item-action product-item" data-value="{{ $product->id }}">
                                            {{ $product->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="product_id" id="selectedProductEdit">
                        @error('product_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Quantity Input -->
                    <div class="form-group">
                        <label for="quantity" class="col-form-label">{{ __('Quantity') }}:</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-cubes"></i></span>
                            </div>
                            <input type="number" min="1" class="form-control" placeholder="Enter quantity" name="quantity" id="quantityEdit" required>
                        </div>
                        @error('quantity')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Status Dropdown -->
                    <!-- <div class="form-group">
                        <label for="status" class="col-form-label">{{ __('Status') }}:</label>
                        <select class="form-control" name="status" id="statusEdit">
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="approved">{{ __('Approved') }}</option>
                            <option value="rejected">{{ __('Rejected') }}</option>
                        </select>
                        @error('status')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div> -->

                    <!-- Notes/Remarks -->
                    <!-- <div class="form-group">
                        <label for="remarks" class="col-form-label">{{ __('Remarks') }}:</label>
                        <textarea class="form-control" name="remarks" id="remarksEdit" rows="2" placeholder="Any additional notes..."></textarea>
                    </div> -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-0">{{ __('Update Request') }}</button>
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
                    <h4 class="modal-title h6">Stock Return Details <span id="viewRequestId"></span></h4>
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
                                        <th>Product:</th>
                                        <td id="viewProductName"></td>
                                    </tr>
                                    <tr>
                                        <th>Quantity:</th>
                                        <td>
                                            <div id="quantityDisplay" class="d-flex align-items-center">
                                                <span id="viewQuantity" class="font-weight-bold mr-2"></span>
                                                
                                                <span id="quantityEditButtonContainer"></span>
                                            </div>
                                            <div id="quantityEditContainer" style="display: none;">
                                                <div class="input-group input-group-sm w-75">
                                                    <input type="number" min="1" class="form-control" id="editQuantityInput" value="">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-success btn-sm" id="saveQuantityBtn">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-secondary btn-sm" id="cancelQuantityEditBtn">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
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
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100">
                        <div id="viewActionButtons">
                            <!-- Approve and Reject buttons will be shown here for pending requests -->
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
        .product-item.active,
        .driver-item:hover,
        .driver-item.active {
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
        #quantityDisplay .btn-link {
            text-decoration: none;
        }

        #quantityEditContainer .input-group {
            max-width: 200px;
        }

        #editQuantityBtn:hover {
            color: #007bff;
        }
    </style>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        var table = window.LaravelDataTables["dataTableBuilder"] || $('.data-table').DataTable();
        
        // Store current request ID for actions
        var currentRequestId = null;
        var currentRequestStatus = null;
        
        // Get base URL for AJAX calls - This is important!
        var baseUrl = window.location.origin;
        
        // Driver selection for both modals
        $('.driver-item').on('click', function(e) {
            e.preventDefault();
            var driverName = $(this).text();
            var driverId = $(this).data('value');
            var dropdown = $(this).closest('.dropdown-menu');
            
            // Update UI
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            dropdown.prev('.dropdown-toggle').text(driverName);
            
            // Set hidden input value
            var modalId = $(this).closest('.modal').attr('id');
            if (modalId === 'createRequest') {
                $('#selectedDriverCreate').val(driverId);
            } else if (modalId === 'editRequest') {
                $('#selectedDriverEdit').val(driverId);
            }
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
            if (modalId === 'createRequest') {
                $('#selectedProductCreate').val(productId);
            } else if (modalId === 'editRequest') {
                $('#selectedProductEdit').val(productId);
            }
        });

        // Search functionality for drivers
        $('#driverSearchCreate, #driverSearchEdit').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            var driverList = $(this).siblings('.list-group');
            
            driverList.find('.driver-item').each(function() {
                var driverText = $(this).text().toLowerCase();
                if (driverText.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Search functionality for products
        $('#productSearchCreate, #productSearchEdit').on('keyup', function() {
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
            if ($(this).text().trim() === '') {
                $(this).text('Select Option');
            }
        });
        $(document).on('click', '#editQuantityBtn', function() {
            var currentQuantity = $('#viewQuantity').text();
            $('#editQuantityInput').val(currentQuantity);
            $('#quantityDisplay').hide();
            $('#quantityEditContainer').show();
            $('#editQuantityInput').focus().select();
        });

        // Handle cancel quantity edit
        $(document).on('click', '#cancelQuantityEditBtn', function() {
            $('#quantityEditContainer').hide();
            $('#quantityDisplay').show();
        });

        // Handle save quantity edit
        $(document).on('click', '#saveQuantityBtn', function() {
            var newQuantity = $('#editQuantityInput').val();
            
            if (!newQuantity || newQuantity < 1) {
                alert('Please enter a valid quantity (minimum 1)');
                return;
            }
            
            updateRequestQuantity(currentRequestId, newQuantity);
        });

        // Handle pressing Enter in quantity input
        $(document).on('keypress', '#editQuantityInput', function(e) {
            if (e.which === 13) { // Enter key
                $('#saveQuantityBtn').click();
            }
        });

        // Function to update request quantity via AJAX
        function updateRequestQuantity(requestId, newQuantity) {
            ShowLoad();
            
            $.ajax({
                url: '{{ route("inventoryRequests.update", ":id") }}'.replace(':id', requestId),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    quantity: newQuantity,
                    _method: 'PUT'
                },
                success: function(response) {
                    HideLoad();
                    if (response.success) {
                        showNotification('success', 'Quantity updated successfully');
                        
                        // Update the displayed quantity
                        $('#viewQuantity').text(newQuantity);
                        
                        // Hide edit container and show display
                        $('#quantityEditContainer').hide();
                        $('#quantityDisplay').show();
                        
                        // Update the DataTable row if needed
                        if (table && typeof table.ajax !== 'undefined') {
                            table.ajax.reload(null, false);
                        }
                    } else {
                        showNotification('error', response.message || 'Failed to update quantity');
                        // Revert to edit mode if error
                        $('#cancelQuantityEditBtn').click();
                    }
                },
                error: function(xhr) {
                    HideLoad();
                    var errorMessage = 'An error occurred while updating quantity';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Show first validation error
                        var errors = xhr.responseJSON.errors;
                        for (var field in errors) {
                            if (errors.hasOwnProperty(field)) {
                                errorMessage = errors[field][0];
                                break;
                            }
                        }
                    }
                    showNotification('error', errorMessage);
                    // Revert to edit mode if error
                    $('#cancelQuantityEditBtn').click();
                }
            });
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
            $('#viewRequestId').text('(# ' + requestId + ')');
            $('#viewRequestIdText').text(requestId);
            
            // Fill view modal with data
            $('#viewDriverName').text(requestData.driver_name);
            $('#viewProductName').text(requestData.product_name);
            $('#viewQuantity').text(requestData.quantity);
            $('#viewRemarks').text(requestData.remarks || 'No remarks');
            $('#viewCreatedAt').text(requestData.created_at);
            
            // Set status with badge
            var status = requestData.status;
            var badgeClass = getStatusBadgeClass(status);
            $('#viewStatusBadge').html('<span class="badge ' + badgeClass + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>');
            
            // Show/hide quantity edit button - ONLY for pending requests
            var quantityEditHtml = '';
            if (status === 'pending') {
                quantityEditHtml = `
                    <button type="button" class="btn btn-sm btn-link p-0" id="editQuantityBtn" title="Edit Quantity">
                        <i class="fa fa-edit"></i>
                    </button>
                `;
            }
            $('#quantityEditButtonContainer').html(quantityEditHtml);
            
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
        // Handle approve action from view modal
        $(document).on('click', '#approveFromViewBtn', function() {
            if (confirm('Are you sure you want to approve this request?')) {
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

        // Handle edit modal opening
        $(document).on('click', '.edit-request-btn', function(e) {
            e.preventDefault();
            var requestId = $(this).data('id');
            var requestData = $(this).data('request');
            
            // Parse JSON string if needed
            if (typeof requestData === 'string') {
                requestData = JSON.parse(requestData);
            }
            
            // Update form action URL - FIXED: Use proper route naming
            var formAction = '{{ route("inventoryRequests.update", ":id") }}';
            formAction = formAction.replace(':id', requestId);
            $('#editRequestForm').attr('action', formAction);
            
            // Fill form with request data
            $('#selectedDriverEdit').val(requestData.driver_id);
            $('#dropdownDriverEdit').text(requestData.driver_name);
            $('#selectedProductEdit').val(requestData.product_id);
            $('#dropdownProductEdit').text(requestData.product_name);
            $('#quantityEdit').val(requestData.quantity);
            $('#statusEdit').val(requestData.status);
            $('#remarksEdit').val(requestData.remarks || '');
            
            // Highlight selected items in dropdowns
            $('#driverListEdit .driver-item').removeClass('active');
            $('#driverListEdit .driver-item[data-value="' + requestData.driver_id + '"]').addClass('active');
            
            $('#productListEdit .product-item').removeClass('active');
            $('#productListEdit .product-item[data-value="' + requestData.product_id + '"]').addClass('active');
            
            // Show modal
            $('#editRequest').modal('show');
        });

        // Clear create modal when closed
        $('#createRequest').on('hidden.bs.modal', function () {
            $('#createRequestForm')[0].reset();
            $('#selectedDriverCreate').val('');
            $('#selectedProductCreate').val('');
            $('#dropdownDriverCreate').text('Select Driver');
            $('#dropdownProductCreate').text('Select Product');
            $('#driverListCreate .driver-item, #productListCreate .product-item').removeClass('active');
        });

        // Clear edit modal when closed
        $('#editRequest').on('hidden.bs.modal', function () {
            $('#editRequestForm')[0].reset();
            $('#selectedDriverEdit').val('');
            $('#selectedProductEdit').val('');
            $('#dropdownDriverEdit').text('Select Driver');
            $('#dropdownProductEdit').text('Select Product');
            $('#driverListEdit .driver-item, #productListEdit .product-item').removeClass('active');
        });

        // Clear view modal when closed
        $('#viewRequest').on('hidden.bs.modal', function () {
            // Reset view modal fields
            $('#viewRequestId').text('');
            $('#viewRequestIdText, #viewDriverName, #viewProductName, #viewQuantity, #viewRemarks, #viewCreatedAt, #viewApprovedBy, #viewApprovedAt, #viewRejectedBy, #viewRejectedAt, #viewRejectionReason').text('');
            $('#viewStatusBadge').html('');
            $('#viewActionButtons').html('');
            currentRequestId = null;
            currentRequestStatus = null;
        });

        // Clear reject modal when closed
        $('#rejectReasonModal').on('hidden.bs.modal', function () {
            $('#rejectRequestId').val('');
            $('#rejection_reason_modal').val('');
        });

        // Handle create form submission
        $('#createRequestForm').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            
            // Validate required fields
            var driverId = $('#selectedDriverCreate').val();
            var productId = $('#selectedProductCreate').val();
            var quantity = $('#quantityCreate').val();
            
            if (!driverId) {
                alert('Please select a driver');
                return false;
            }
            
            if (!productId) {
                alert('Please select a product');
                return false;
            }
            
            if (!quantity || quantity < 1) {
                alert('Please enter a valid quantity (minimum 1)');
                return false;
            }
            
            ShowLoad();
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    HideLoad();
                    if (response.success) {
                        $('#createRequest').modal('hide');
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
                    showNotification('error', xhr.responseJSON?.message || 'An error occurred');
                }
            });
        });

        // Handle edit form submission
        $('#editRequestForm').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            
            // Validate required fields
            var driverId = $('#selectedDriverEdit').val();
            var productId = $('#selectedProductEdit').val();
            var quantity = $('#quantityEdit').val();
            
            if (!driverId) {
                alert('Please select a driver');
                return false;
            }
            
            if (!productId) {
                alert('Please select a product');
                return false;
            }
            
            if (!quantity || quantity < 1) {
                alert('Please enter a valid quantity (minimum 1)');
                return false;
            }
            
            ShowLoad();
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                headers: {
                    'X-HTTP-Method-Override': 'PUT'
                },
                success: function(response) {
                    HideLoad();
                    if (response.success) {
                        $('#editRequest').modal('hide');
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
                    showNotification('error', xhr.responseJSON?.message || 'An error occurred');
                }
            });
        });

        // Handle cancel action via AJAX
        $(document).on('submit', 'form[action*="cancel"]', function(e) {
            e.preventDefault();
            var form = $(this);
            
            if (confirm('Are you sure you want to cancel this request?')) {
                ShowLoad();
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
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

        // Handle delete action via AJAX
        $(document).on('submit', 'form[action*="destroy"]', function(e) {
            e.preventDefault();
            var form = $(this);
            
            if (confirm('Are you sure you want to delete this request?')) {
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

        // Helper function to approve request - FIXED URL
        function approveRequest(requestId) {
            ShowLoad();
            
            // Use Laravel route helper via Blade
            var url = '{{ route("inventoryRequests.approve", ":id") }}';
            url = url.replace(':id', requestId);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
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
                        showNotification('error', response.message || 'An error occurred');
                    }
                },
                error: function(xhr) {
                    HideLoad();
                    var errorMessage = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMessage = 'Request not found. It may have been deleted.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'You do not have permission to approve this request.';
                    }
                    showNotification('error', errorMessage);
                }
            });
        }

        // Helper function to reject request - FIXED URL
        function rejectRequest(requestId, rejectReason, modal = null) {
            ShowLoad();
            
            // Use Laravel route helper via Blade
            var url = '{{ route("inventoryRequests.reject", ":id") }}';
            url = url.replace(':id', requestId);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    rejection_reason: rejectReason
                },
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
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Handle validation errors
                        var errors = xhr.responseJSON.errors;
                        for (var field in errors) {
                            if (errors.hasOwnProperty(field)) {
                                errorMessage = errors[field][0];
                                break;
                            }
                        }
                    } else if (xhr.status === 404) {
                        errorMessage = 'Request not found. It may have been deleted.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'You do not have permission to reject this request.';
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
    });

    // Keyboard shortcut for creating new request
    $(document).keyup(function(e) {
        if(e.altKey && e.keyCode == 78 && ($('#createRequest').length > 0)) {
            $('#createRequest').modal('show');
        }
    });
</script>
@endpush