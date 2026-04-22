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

    <!-- Create Request Modal - Enhanced -->
    <div id="createRequest" class="modal fade" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1400px;">
            <div class="modal-content" style="border-radius: 12px; max-height: 90vh; display: flex; flex-direction: column;">
                <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e9ecef; flex-shrink: 0;">
                    <h4 class="modal-title h5 font-weight-bold">
                        <i class="fa fa-plus-circle text-primary mr-2"></i>{{ __('Create Stock Request') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body" style="padding: 1.5rem; overflow-y: auto; flex: 1; max-height: calc(100vh - 200px);">
                    {!! Form::open(['route' => 'inventoryRequests.store', 'enctype' => 'multipart/form-data', 'id' => 'createRequestForm']) !!}
                    
                    <div class="row">
                        <!-- Driver Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver_id" class="col-form-label font-weight-bold">{{ __('Driver') }} <span class="text-danger">*</span>:</label>
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary btn-block dropdown-toggle text-left" type="button" id="dropdownDriverCreate" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 10px 15px;">
                                        <i class="fa fa-user mr-2"></i>{{ __('Select Driver') }}
                                    </button>
                                    <div class="dropdown-menu p-3" aria-labelledby="dropdownDriverCreate" style="width: 100%; max-height: 350px; overflow-y: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="driverSearchCreate" placeholder="Search Drivers...">
                                        </div>
                                        <div id="driverListCreate" class="list-group" style="max-height: 250px; overflow-y: auto;">
                                            @foreach($drivers as $driver)
                                                <a href="#" class="list-group-item list-group-item-action driver-item" data-value="{{ $driver->id }}">
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
                            <div class="form-group">
                                <label for="remarks" class="col-form-label font-weight-bold">{{ __('Remarks') }} ({{ __('Optional') }}):</label>
                                <textarea class="form-control" name="remarks" id="remarks" rows="3" placeholder="Any additional notes..." style="resize: vertical;"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Table - Scrollable wrapper -->
                    <div class="form-group mt-3">
                        <label class="col-form-label font-weight-bold">{{ __('Items') }} <span class="text-danger">*</span>:</label>
                        
                        <!-- Scrollable wrapper for items table -->
                        <div class="items-scroll-wrapper" style="max-height: 450px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; background: #fff;">
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="table table-bordered mb-0" id="itemsTable" style="min-width: 700px;">
                                    <thead class="bg-light position-sticky top-0" style="position: sticky; top: 0; z-index: 10; background: #f8f9fa;">
                                        <tr>
                                            <th width="5%" class="text-center">#</th>
                                            <th width="55%">Product <span class="text-danger">*</span></th>
                                            <th width="25%">Quantity <span class="text-danger">*</span></th>
                                            <th width="15%" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        <!-- Items will be added here dynamically -->
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="4" class="text-right py-2">
                                                <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                                                    <i class="fa fa-plus mr-1"></i> Add Item
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="text-danger small mt-1" id="itemsError"></div>
                    </div>

                    <div class="modal-footer" style="border-top: 1px solid #e9ecef; margin-top: 20px; flex-shrink: 0;">
                        <button type="button" class="btn btn-secondary rounded-0 px-4" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-0 px-4">{{ __('Submit Request') }}</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Request Modal - Enhanced -->
    <div id="editRequest" class="modal fade" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1400px;">
            <div class="modal-content" style="border-radius: 12px; max-height: 90vh; display: flex; flex-direction: column;">
                <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e9ecef; flex-shrink: 0;">
                    <h4 class="modal-title h5 font-weight-bold">
                        <i class="fa fa-pencil-square-o text-warning mr-2"></i>{{ __('Edit Stock Request') }}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body" style="padding: 1.5rem; overflow-y: auto; flex: 1; max-height: calc(100vh - 200px);">
                    {!! Form::open(['route' => ['inventoryRequests.update', ':id'], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'id' => 'editRequestForm']) !!}
                    
                    <!-- Add hidden field -->
                    <input type="hidden" name="save_and_approve" id="saveAndApprove" value="0">
                    
                    <div class="row">
                        <!-- Driver Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver_id" class="col-form-label font-weight-bold">{{ __('Driver') }} <span class="text-danger">*</span>:</label>
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary btn-block dropdown-toggle text-left" type="button" id="dropdownDriverEdit" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 10px 15px;">
                                        <i class="fa fa-user mr-2"></i>{{ __('Select Driver') }}
                                    </button>
                                    <div class="dropdown-menu p-3" aria-labelledby="dropdownDriverEdit" style="width: 100%; max-height: 350px; overflow-y: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="driverSearchEdit" placeholder="Search Drivers...">
                                        </div>
                                        <div id="driverListEdit" class="list-group" style="max-height: 250px; overflow-y: auto;">
                                            @foreach($drivers as $driver)
                                                <a href="#" class="list-group-item list-group-item-action driver-item" data-value="{{ $driver->id }}">
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
                            <div class="form-group">
                                <label for="remarks" class="col-form-label font-weight-bold">{{ __('Remarks') }} ({{ __('Optional') }}):</label>
                                <textarea class="form-control" name="remarks" id="remarksEdit" rows="3" placeholder="Any additional notes..." style="resize: vertical;"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Table - Scrollable wrapper -->
                    <div class="form-group mt-3">
                        <label class="col-form-label font-weight-bold">{{ __('Items') }} <span class="text-danger">*</span>:</label>
                        
                        <!-- Scrollable wrapper for items table -->
                        <div class="items-scroll-wrapper" style="max-height: 450px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; background: #fff;">
                            <div class="table-responsive" style="overflow-x: auto;">
                                <table class="table table-bordered mb-0" id="editItemsTable" style="min-width: 700px;">
                                    <thead class="bg-light position-sticky top-0" style="position: sticky; top: 0; z-index: 10; background: #f8f9fa;">
                                        <tr>
                                            <th width="5%" class="text-center">#</th>
                                            <th width="55%">Product <span class="text-danger">*</span></th>
                                            <th width="25%">Quantity <span class="text-danger">*</span></th>
                                            <th width="15%" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="editItemsBody">
                                        <!-- Items will be populated here -->
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="4" class="text-right py-2">
                                                <button type="button" class="btn btn-success btn-sm" id="addEditItemBtn">
                                                    <i class="fa fa-plus mr-1"></i> Add Item
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="text-danger small mt-1" id="editItemsError"></div>
                    </div>

                    <div class="modal-footer" style="border-top: 1px solid #e9ecef; margin-top: 20px; flex-shrink: 0;">
                        <button type="button" class="btn btn-secondary rounded-0 px-4" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-0 px-4">{{ __('Update Request') }}</button>
                        
                        @if(auth()->user()->hasRole('admin'))
                            <button type="button" class="btn btn-success rounded-0 px-4" id="saveAndApproveBtn">
                                <i class="fa fa-check mr-1"></i> {{ __('Save & Approve') }}
                            </button>
                        @endif
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Request Modal - Enhanced -->
    <div id="viewRequest" class="modal fade" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 900px;">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                    <h4 class="modal-title h5 font-weight-bold">
                        <i class="fa fa-info-circle text-info mr-2"></i>Stock Request Details <span id="viewRequestId" class="text-muted"></span>
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body" style="padding: 2rem;">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr style="background-color: #fafafa;">
                                        <th width="30%" style="background-color: #f5f5f5;">Request ID:</th>
                                        <td id="viewRequestIdText"></td>
                                    </tr>
                                    <tr style="background-color: #fafafa;">
                                        <th style="background-color: #f5f5f5;">Driver:</th>
                                        <td id="viewDriverName"></td>
                                    </tr>
                                    <tr style="background-color: #fafafa;">
                                        <th style="background-color: #f5f5f5;">Status:</th>
                                        <td><span id="viewStatusBadge"></span></td>
                                    </tr>
                                    <tr style="background-color: #fafafa;">
                                        <th style="background-color: #f5f5f5;">Remarks:</th>
                                        <td id="viewRemarks">—</td>
                                    </tr>
                                    <tr style="background-color: #fafafa;">
                                        <th style="background-color: #f5f5f5;">Requested At:</th>
                                        <td id="viewCreatedAt">—</td>
                                    </tr>
                                    <tr id="viewApprovedSection" style="display: none;">
                                        <th style="background-color: #f5f5f5;">Approved By:</th>
                                        <td id="viewApprovedBy">—</td>
                                    </tr>
                                    <tr id="viewApprovedAtSection" style="display: none;">
                                        <th style="background-color: #f5f5f5;">Approved At:</th>
                                        <td id="viewApprovedAt">—</td>
                                    </tr>
                                    <tr id="viewRejectedSection" style="display: none;">
                                        <th style="background-color: #f5f5f5;">Rejected By:</th>
                                        <td id="viewRejectedBy">—</td>
                                    </tr>
                                    <tr id="viewRejectedAtSection" style="display: none;">
                                        <th style="background-color: #f5f5f5;">Rejected At:</th>
                                        <td id="viewRejectedAt">—</td>
                                    </tr>
                                    <tr id="viewRejectionReasonSection" style="display: none;">
                                        <th style="background-color: #f5f5f5;">Rejection Reason:</th>
                                        <td id="viewRejectionReason">—</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Items Table Section -->
                    <div class="mt-4">
                        <h5 class="font-weight-bold mb-3"><i class="fa fa-list mr-2"></i>Requested Items</h5>
                        <div id="viewItemsTable"></div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                    <div class="d-flex justify-content-between w-100">
                        <div id="viewActionButtons">
                            <!-- Action buttons will be shown here -->
                        </div>
                        <button type="button" class="btn btn-secondary rounded-0 px-4" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Reason Modal (for reject action) -->
    <div id="rejectReasonModal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e9ecef;">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fa fa-times-circle text-danger mr-2"></i>Reject Request
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <input type="hidden" id="rejectRequestId" value="">
                    <div class="form-group">
                        <label for="rejection_reason_modal" class="font-weight-bold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="rejection_reason_modal" class="form-control" rows="3" placeholder="Please provide a reason for rejection..." style="border-radius: 8px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-secondary rounded-0 px-4" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger rounded-0 px-4" id="confirmRejectBtn">
                        <i class="fa fa-check mr-1"></i> Confirm Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<style>
    /* ============================================
   COMPLETE FIXED CSS FOR MODAL SCROLLING
   ============================================ */

/* Fix modal scrolling for multiple items - CRITICAL */
.modal {
    overflow-y: auto !important;
    overflow-x: hidden !important;
}

.modal-open {
    overflow: hidden !important;
    padding-right: 0 !important;
}

.modal-dialog {
    margin: 1.75rem auto !important;
    max-height: calc(100vh - 3.5rem) !important;
}

.modal-content {
    max-height: calc(100vh - 3.5rem) !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.modal-header {
    flex-shrink: 0 !important;
    background: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
    padding: 1.25rem 1.5rem;
}

.modal-body {
    flex: 1 1 auto !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    padding: 1.5rem !important;
    max-height: none !important;
}

.modal-footer {
    flex-shrink: 0 !important;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}

/* Fix for the items scroll wrapper - CRITICAL */
.items-scroll-wrapper {
    max-height: 400px !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #fff;
}

/* Ensure table stays within wrapper */
.items-scroll-wrapper .table-responsive {
    overflow-x: auto !important;
    overflow-y: visible !important;
    max-height: none !important;
}

/* Sticky header for items table - CRITICAL */
#itemsTable thead th,
#editItemsTable thead th {
    position: sticky !important;
    top: 0 !important;
    background: #f8f9fa !important;
    z-index: 10 !important;
    box-shadow: inset 0 1px 0 #dee2e6, inset 0 -1px 0 #dee2e6;
}

/* Sticky footer for items table */
#itemsTable tfoot,
#editItemsTable tfoot {
    position: sticky !important;
    bottom: 0 !important;
    background: #f8f9fa !important;
    z-index: 10 !important;
}

/* Fix for the items table container */
#itemsTable, #editItemsTable {
    margin-bottom: 0;
    overflow: visible !important;
}

/* Ensure the table wrapper scrolls properly */
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

/* ============================================
   FIXED DROPDOWN STYLES - SEPARATE BY TYPE
   ============================================ */

/* Regular dropdown (driver selection) - normal behavior */

.dropdown-menu.show {
    display: block !important;
}

/* Ensure product dropdown closes properly */
.item-row .dropdown .dropdown-menu,
.edit-item-row .dropdown .dropdown-menu {
    position: fixed !important;
    display: none !important;
}

.item-row .dropdown.show .dropdown-menu,
.edit-item-row .dropdown.show .dropdown-menu {
    display: block !important;
}

/* Fix for Bootstrap dropdown not closing */
.dropdown .dropdown-menu {
    display: none;
}

.dropdown.show .dropdown-menu {
    display: block;
}

.dropdown:not(.item-row .dropdown, .edit-item-row .dropdown) .dropdown-menu {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    right: auto !important;
    margin-top: 5px !important;
    margin-bottom: 0 !important;
    bottom: auto !important;
    z-index: 9999 !important;
}

/* Product dropdown in table rows - fixed positioning for scrolling */
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

/* General dropdown styles */
.dropdown {
    position: relative;
}

.dropdown-menu {
    max-height: 350px;
    overflow-y: auto;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: 1px solid #e0e0e0;
    background: white;
    will-change: transform;
}

/* Specific styles for driver dropdown */
#dropdownDriverCreate + .dropdown-menu,
#dropdownDriverEdit + .dropdown-menu {
    width: 100% !important;
    min-width: 100% !important;
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

/* Fix dropdown positioning inside scrolling modal - only for product dropdowns */
.modal-body .item-row .dropdown-menu,
.modal-body .edit-item-row .dropdown-menu {
    position: fixed !important;
    z-index: 1060 !important;
}

/* Ensure proper spacing when many items */
.item-row td, .edit-item-row td {
    overflow: visible !important;
    position: static !important;
}

.item-row .dropdown, .edit-item-row .dropdown {
    position: static !important;
}

/* View modal items table scrolling - CRITICAL */
#viewItemsTable {
    max-height: 400px;
    overflow-y: auto;
    overflow-x: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
}

#viewItemsTable .table {
    margin-bottom: 0;
}

#viewItemsTable .table th {
    background-color: #f8f9fa;
}

/* Modal XL sizing */
.modal-xl {
    max-width: 1400px;
}

/* Button styling */
.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
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

/* Error styling */
.is-invalid {
    border-color: #dc3545 !important;
}

.text-danger {
    font-size: 0.75rem;
}

/* Badge styles */
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

/* Driver and product selection styles */
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

.small {
    font-size: 80%;
}

/* Ensure dropdowns from table rows are visible above modal content */
.item-row .dropdown.show,
.edit-item-row .dropdown.show {
    position: relative;
    z-index: 10000;
}

/* Fix for any hidden overflow on parent elements */
.container-fluid, 
.animated, 
.fadeIn, 
.row, 
.col-lg-12, 
.card, 
.card-body {
    overflow: visible !important;
}

/* Ensure modal backdrop doesn't cause issues */
.modal-backdrop {
    z-index: 1040 !important;
}

/* Card body overflow fix */
.card-body {
    overflow: visible !important;
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
    
    .items-scroll-wrapper {
        max-height: 300px !important;
    }
    
    #viewItemsTable {
        max-height: 300px;
    }
}

@media (max-width: 768px) {
    .modal-dialog {
        margin: 0.5rem !important;
        max-height: calc(100vh - 1rem) !important;
    }
    
    .modal-content {
        max-height: calc(100vh - 1rem) !important;
    }
}
</style>

<script>
    // Auto-open script for inventory requests
    $(document).ready(function() {

        $('.modal').on('show.bs.modal', function() {
            $('body').addClass('modal-open');
            $('body').css('overflow', 'hidden');
            $('body').css('padding-right', '0');
            
            // Fix dropdown positioning in scrolling modal
            setTimeout(function() {
                $('.modal.show').each(function() {
                    $(this).find('.dropdown-menu').each(function() {
                        $(this).css('position', 'fixed');
                    });
                });
            }, 100);
        });
        
        $('.modal').on('hidden.bs.modal', function() {
            $('body').removeClass('modal-open');
            $('body').css('overflow', '');
            $('body').css('padding-right', '');
        });
        
        // Prevent modal from closing when clicking inside modal body
        $('.modal').on('click', function(e) {
            if ($(e.target).hasClass('modal')) {
                // Don't auto-close, only close when clicking close button
                e.preventDefault();
            }
        });
        
        // Fix for dropdowns in modals
        $(document).on('shown.bs.dropdown', '.dropdown', function() {
            var $menu = $(this).find('.dropdown-menu');
            var $button = $(this).find('.dropdown-toggle');
            
            // Check if this is a product dropdown inside table row
            if ($(this).closest('.item-row, .edit-item-row').length > 0) {
                // Product dropdown - use fixed positioning
                var buttonOffset = $button.offset();
                $menu.css({
                    'position': 'fixed',
                    'top': buttonOffset.top + $button.outerHeight(),
                    'left': buttonOffset.left,
                    'z-index': 1060,
                    'min-width': '600px',
                    'max-width': '600px'
                });
            } else {
                // Driver dropdown - use normal absolute positioning
                $menu.css({
                    'position': 'absolute',
                    'top': '100%',
                    'left': '0',
                    'right': 'auto',
                    'z-index': 9999
                });
            }
        });
        
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
        
        // Check URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const viewRequestId = urlParams.get('view_request');
        
        if (viewRequestId) {
            console.log('Found view_request parameter:', viewRequestId);
            localStorage.setItem('pendingInventoryRequestModal', viewRequestId);
            
            // Clean up URL
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
        
        // Hook into DataTable initialization
        if (typeof $.fn.DataTable !== 'undefined') {
            $(document).on('init.dt', function(e, settings) {
                console.log('Inventory Requests DataTable initialized');
                
                // Get the DataTable instance
                const table = $(settings.nTable).DataTable();
                
                // Hook into draw event
                table.on('draw', function() {
                    console.log('Inventory Requests DataTable draw event');
                    
                    // Check for pending modal
                    const pendingId = localStorage.getItem('pendingInventoryRequestModal');
                    if (pendingId) {
                        console.log('Attempting to open modal for request ID:', pendingId);
                        
                        // Try to find and click the button
                        setTimeout(function() {
                            const button = $('.view-request-btn[data-id="' + pendingId + '"]');
                            if (button.length) {
                                console.log('Found view button, clicking...');
                                button.click();
                                localStorage.removeItem('pendingInventoryRequestModal');
                            } else {
                                console.log('View button not found yet, will retry...');
                            }
                        }, 1000);
                    }
                });
            });
        }
        
        // Also check after page load
        setTimeout(function() {
            const pendingId = localStorage.getItem('pendingInventoryRequestModal');
            if (pendingId) {
                console.log('Page load check - Pending request ID:', pendingId);
                
                // Try multiple times
                let attempts = 0;
                const maxAttempts = 5;
                
                function tryOpenModal() {
                    attempts++;
                    console.log(`Attempt ${attempts} to find button for ID: ${pendingId}`);
                    
                    const button = $('.view-request-btn[data-id="' + pendingId + '"]');
                    if (button.length) {
                        console.log('Found button on attempt', attempts);
                        button.click();
                        localStorage.removeItem('pendingInventoryRequestModal');
                    } else if (attempts < maxAttempts) {
                        setTimeout(tryOpenModal, 1000);
                    } else {
                        console.log('Could not find button after', maxAttempts, 'attempts');
                    }
                }
                
                tryOpenModal();
            }
        }, 2000);
        
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
        
        // Add item row to create modal
        function addItemRow() {
            var row = `
                <tr class="item-row" data-index="${itemCounter}">
                    <td class="align-middle text-center font-weight-bold">${itemCounter + 1}</td>
                    <td style="min-width: 400px;">
                        <div class="dropdown w-100">
                            <button class="btn btn-outline-secondary btn-block dropdown-toggle text-left product-dropdown" type="button" id="productDropdown${itemCounter}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="overflow: visible; text-overflow: ellipsis; padding: 8px 12px;">
                                <i class="fa fa-cube mr-2"></i>{{ __('Select Product') }}
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="productDropdown${itemCounter}" style="width: 600px; min-width: 600px; max-height: 350px; overflow-y: auto;">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control product-search" placeholder="Search Products..." data-index="${itemCounter}">
                                </div>
                                <div class="product-list" data-index="${itemCounter}" style="max-height: 230px; overflow-y: auto;">
                                    @foreach($products as $product)
                                        <a href="#" class="list-group-item list-group-item-action product-select-item" 
                                        data-index="${itemCounter}" 
                                        data-value="{{ $product->id }}" 
                                        data-name="{{ $product->name }}" 
                                        data-code="{{ $product->code }}">                                            
                                            <i class="fa fa-cube mr-2 text-secondary"></i>{{ $product->name }} ({{ $product->code }})
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="product-id-input" name="items[${itemCounter}][product_id]" value="">
                        <div class="text-danger product-error small mt-1"></div>
                    </div>
                    <td style="min-width: 140px;">
                        <input type="number" min="1" class="form-control quantity-input" name="items[${itemCounter}][quantity]" placeholder="Enter quantity" style="padding: 8px 12px;">
                        <div class="text-danger quantity-error small mt-1"></div>
                    </div>
                    <td class="align-middle text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-item-btn" ${itemCounter === 0 ? 'disabled' : ''}>
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </tr>
            `;
            
            $('#itemsBody').append(row);
            itemCounter++;
            
            // Enable remove buttons if more than one row
            if ($('#itemsBody tr').length > 1) {
                $('#itemsBody tr:first .remove-item-btn').prop('disabled', false);
            }
            
            // If driver is already selected, filter products for the new row
            var driverId = $('#selectedDriverCreate').val();
            if (driverId) {
                filterProductsByDriver(driverId);
            }
        }
        
        // Driver selection for create modal
        $(document).on('click', '#driverListCreate .driver-item', function(e) {
            e.preventDefault();
            var driverName = $(this).text().trim();
            var driverId = $(this).data('value');
            
            console.log('Driver selected:', driverId, driverName);
            
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            $('#dropdownDriverCreate').html(`<i class="fa fa-user mr-2"></i>${driverName}`);
            $('#selectedDriverCreate').val(driverId);
            $('#driverError').text('');
            
            // Close any open product dropdowns
            $('.dropdown.show').removeClass('show');
            $('.dropdown-menu.show').removeClass('show');
                        
            filterProductsByDriver(driverId);
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

        // Function to restore all products (when driver changes)
        function restoreAllProducts() {
            console.log('Restoring all products');
            
            // Get the original products data from the server
            var originalProducts = @json($products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code
                ];
            }));
            
            // For each product list in each row, restore all products
            $('.product-list').each(function() {
                var $list = $(this);
                var currentIndex = $list.data('index');
                
                // Clear current list
                $list.empty();
                
                // Add back all products
                originalProducts.forEach(function(product) {
                    $list.append(`
                        <a href="#" class="list-group-item list-group-item-action product-select-item" 
                            data-index="${currentIndex}" 
                            data-value="${product.id}" 
                            data-name="${product.name}" 
                            data-code="${product.code}">                                            
                            <i class="fa fa-cube mr-2 text-secondary"></i>${product.name} (${product.code})
                        </a>
                    `);
                });
                
                // Remove any "no products" message
                $list.find('.no-products-message').remove();
            });
        }

        function filterProductsByDriver(driverId) {
            
            if (!driverId) {
                console.log('No driver ID provided');
                return;
            }

            restoreAllProducts();

            // Get the selected driver's blocked products
            var url = '/driver/' + driverId + '/blocked-products';
            console.log('AJAX URL:', url);
            
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var blockedProductIds = response.blocked_product_ids;
                        
                        // Store blocked IDs globally
                        window.blockedProductIds = blockedProductIds;
                        
                        // COMPLETELY REMOVE blocked products from the dropdown
                        $('.product-list .product-select-item').each(function() {
                            var productId = $(this).data('value');
                            
                            if (blockedProductIds.includes(productId)) {
                                console.log('Removing blocked product:', productId);
                                // Completely remove the blocked product item
                                $(this).remove();
                            }
                        });
                        
                        // Also check existing selected products in rows and clear them
                        $('#itemsBody tr').each(function() {
                            var productId = $(this).find('.product-id-input').val();
                            var productError = $(this).find('.product-error');
                            var $dropdownBtn = $(this).find('.product-dropdown');
                            
                            if (productId && blockedProductIds.includes(parseInt(productId))) {
                                console.log('Clearing blocked selected product:', productId);
                                // Clear the blocked product selection
                                $(this).find('.product-id-input').val('');
                                $dropdownBtn.html('<i class="fa fa-cube mr-2"></i>{{ __('Select Product') }}');
                                productError.text('');
                            }
                        });
                        
                        // Show message if no products available
                        $('.product-list').each(function() {
                            var $list = $(this);
                            var hasProducts = $list.find('.product-select-item').length > 0;
                            
                            // Remove existing message
                            $list.find('.no-products-message').remove();
                            
                            if (!hasProducts) {
                                $list.append('<div class="alert alert-warning no-products-message mt-2">No products available for this driver</div>');
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', error);
                }
            });
        }

        // Product selection in create modal 
        $(document).on('click', '.product-select-item', function(e) {
            e.preventDefault();
            var driverId = $('#selectedDriverCreate').val();
            
            // Check if driver is selected
            if (!driverId) {
                alert('Please select a driver first');
                return;
            }
            
            var index = $(this).data('index');
            var productId = $(this).data('value');
            var productName = $(this).data('name');
            var productCode = $(this).data('code');
            
            // Show product name with code
            $('#productDropdown' + index).html(`<i class="fa fa-cube mr-2"></i>${productName} (${productCode})`).attr('title', `${productName} (${productCode})`);
            
            // Set the hidden input value
            $(this).closest('tr').find('.product-id-input').val(productId);
            
            // Clear error
            $(this).closest('tr').find('.product-error').text('');
            
            // Highlight selected item
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            
            // Close the dropdown
            $(this).closest('.dropdown').removeClass('show');
            $(this).closest('.dropdown-menu').removeClass('show');
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
            $('#dropdownDriverCreate').html('<i class="fa fa-user mr-2"></i>{{ __('Select Driver') }}');
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
        }
        
        // Add item row to edit modal
        function addEditItemRow(productId = '', productName = '', productCode = '', quantity = '') {
            var displayName = productName ? `${productName} (${productCode})` : '{{ __('Select Product') }}';
            var currentIndex = editItemCounter;
            
            var row = `
                <tr class="edit-item-row" data-index="${currentIndex}">
                    <td class="align-middle text-center font-weight-bold">${currentIndex + 1}</td>
                    <td style="min-width: 600px;">
                        <div class="dropdown w-100">
                            <button class="btn btn-outline-secondary btn-block dropdown-toggle text-left edit-product-dropdown" type="button" id="editProductDropdown${currentIndex}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="overflow: visible; text-overflow: ellipsis; padding: 8px 12px;">
                                <i class="fa fa-cube mr-2"></i>${displayName}
                            </button>
                            <div class="dropdown-menu p-3" aria-labelledby="editProductDropdown${currentIndex}" style="width: 100%; min-width: 600px; max-height: 350px; overflow-y: auto;">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="fa fa-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control edit-product-search" placeholder="Search Products..." data-index="${currentIndex}">
                                </div>
                                <div class="edit-product-list" data-index="${currentIndex}" style="max-height: 230px; overflow-y: auto;">
                                    <!-- Products will be populated here -->
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="edit-product-id-input" name="items[${currentIndex}][product_id]" value="${productId}">
                        <div class="text-danger edit-product-error small mt-1"></div>
                    </div>
                    <td style="min-width: 150px;">
                        <input type="number" min="1" class="form-control edit-quantity-input" name="items[${currentIndex}][quantity]" placeholder="Enter quantity" value="${quantity}" style="padding: 8px 12px;">
                        <div class="text-danger edit-quantity-error small mt-1"></div>
                    </div>
                    <td class="align-middle text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-edit-item-btn" ${currentIndex === 0 ? 'disabled' : ''}>
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            $('#editItemsBody').append(row);
            
            // Populate products from original list for this new row
            var originalProducts = @json($products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code
                ];
            }));
            
            var $productList = $(`#editItemsBody tr:last .edit-product-list`);
            $productList.data('index', currentIndex);
            
            originalProducts.forEach(function(product) {
                $productList.append(`
                    <a href="#" class="list-group-item list-group-item-action edit-product-select-item" 
                        data-index="${currentIndex}" 
                        data-value="${product.id}" 
                        data-name="${product.name}" 
                        data-code="${product.code}">                                            
                        <i class="fa fa-cube mr-2 text-secondary"></i>${product.name} (${product.code})
                    </a>
                `);
            });
            
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
            $('#dropdownDriverEdit').html(`<i class="fa fa-user mr-2"></i>${requestData.driver_name || 'Select Driver'}`);
            $('#remarksEdit').val(requestData.remarks || '');
            
            // Highlight selected driver
            $('#driverListEdit .driver-item').removeClass('active');
            $('#driverListEdit .driver-item[data-value="' + requestData.driver_id + '"]').addClass('active');
            
            // Clear and populate items table
            $('#editItemsBody').empty();
            editItemCounter = 0;
            
            if (requestData.items && Array.isArray(requestData.items) && requestData.items.length > 0) {
                requestData.items.forEach(function(item, index) {
                    var productCode = '';
                    if (productsLookup[item.product_id]) {
                        productCode = productsLookup[item.product_id].code;
                    }
                    var productName = getProductName(item.product_id);
                    addEditItemRow(item.product_id, productName, productCode, item.quantity);
                });
            } else {
                // For backward compatibility with old single-item requests
                if (requestData.product_id && requestData.quantity) {
                    var productName = getProductName(requestData.product_id);
                    addEditItemRow(requestData.product_id, productName, '', requestData.quantity);
                }
            }
            
            // Show modal
            $('#editRequest').modal('show');
            
            // AFTER modal is shown, filter products based on the selected driver
            setTimeout(function() {
                var driverId = $('#selectedDriverEdit').val();
                if (driverId) {
                    console.log('Filtering edit modal products for driver:', driverId);
                    filterProductsForEditModal(driverId);
                }
            }, 200);
        });
        
        // Function to restore all products for edit modal (when driver changes)
        function restoreAllProductsForEditModal() {
            
            // Get the original products data from the server
            var originalProducts = @json($products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code
                ];
            }));
            
            // For each product list in each edit row, restore all products
            $('.edit-product-list').each(function() {
                var $list = $(this);
                var currentIndex = $list.data('index');
                
                if (currentIndex === undefined || currentIndex === null) {
                    // Try to get index from the dropdown button or parent row
                    var $row = $list.closest('tr');
                    if ($row.length) {
                        currentIndex = $row.data('index');
                        $list.data('index', currentIndex);
                    }
                }
                
                console.log('Restoring product list for index:', currentIndex);
                
                // Clear current list
                $list.empty();
                
                // Add back all products
                originalProducts.forEach(function(product) {
                    $list.append(`
                        <a href="#" class="list-group-item list-group-item-action edit-product-select-item" 
                            data-index="${currentIndex}" 
                            data-value="${product.id}" 
                            data-name="${product.name}" 
                            data-code="${product.code}">                                            
                            <i class="fa fa-cube mr-2 text-secondary"></i>${product.name} (${product.code})
                        </a>
                    `);
                });
                
                // Remove any "no products" message
                $list.find('.no-products-message').remove();
            });
        }

        // Driver selection for edit modal
        $(document).on('click', '#driverListEdit .driver-item', function(e) {
            e.preventDefault();
            var driverName = $(this).text().trim();
            var driverId = $(this).data('value');
            
            console.log('Edit modal - Driver selected:', driverId, driverName);
            
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            $('#dropdownDriverEdit').html(`<i class="fa fa-user mr-2"></i>${driverName}`);
            $('#selectedDriverEdit').val(driverId);
            $('#driverEditError').text('');
            
            // Close any open product dropdowns
            $('.dropdown.show').removeClass('show');
            $('.dropdown-menu.show').removeClass('show');
            
            // Filter products for edit modal
            filterProductsForEditModal(driverId);
        });

        function filterProductsForEditModal(driverId) {
            console.log('filterProductsForEditModal called with driverId:', driverId);
            
            if (!driverId) {
                console.log('No driver ID provided');
                return;
            }
            
            // FIRST: Restore all products for edit modal
            restoreAllProductsForEditModal();
            
            // Get the selected driver's blocked products
            var url = '/driver/' + driverId + '/blocked-products';
            console.log('AJAX URL for edit:', url);
            
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX Response for edit:', response);
                    if (response.success) {
                        var blockedProductIds = response.blocked_product_ids;
                        console.log('Blocked product IDs for edit:', blockedProductIds);
                        
                        // Store blocked IDs globally
                        window.blockedProductIds = blockedProductIds;
                        
                        // COMPLETELY REMOVE blocked products from edit modal dropdown
                        $('.edit-product-list .edit-product-select-item').each(function() {
                            var productId = $(this).data('value');
                            
                            if (blockedProductIds.includes(productId)) {
                                console.log('Removing blocked product from edit:', productId);
                                $(this).remove();
                            }
                        });
                        
                        // Check existing selected products in edit rows
                        $('#editItemsBody tr').each(function() {
                            var productId = $(this).find('.edit-product-id-input').val();
                            var productError = $(this).find('.edit-product-error');
                            var $dropdownBtn = $(this).find('.edit-product-dropdown');
                            
                            if (productId && blockedProductIds.includes(parseInt(productId))) {
                                console.log('Clearing blocked selected product in edit:', productId);
                                // Clear the blocked product selection
                                $(this).find('.edit-product-id-input').val('');
                                $dropdownBtn.html('<i class="fa fa-cube mr-2"></i>{{ __('Select Product') }}');
                                productError.text('⚠️ This product is blocked for the selected driver');
                            } else if (productId) {
                                productError.text('');
                            }
                        });
                        
                        // Show message if no products available
                        $('.edit-product-list').each(function() {
                            var $list = $(this);
                            var hasProducts = $list.find('.edit-product-select-item').length > 0;
                            
                            $list.find('.no-products-message').remove();
                            
                            if (!hasProducts) {
                                $list.append('<div class="alert alert-warning no-products-message mt-2">No products available for this driver</div>');
                            }
                        });
                    } else {
                        console.log('Response success false:', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error for edit - Status:', status);
                    console.log('AJAX Error for edit - Error:', error);
                    console.log('AJAX Error for edit - Response:', xhr.responseText);
                }
            });
        }
        // Product search in edit modal
        $(document).on('keyup', '.edit-product-search', function() {
            var searchTerm = $(this).val().toLowerCase();
            var index = $(this).data('index');
            
            // Find the product list - it's within the same dropdown-menu, not sibling
            var dropdownMenu = $(this).closest('.dropdown-menu');
            var productList = dropdownMenu.find('.edit-product-list');
            
            console.log('Searching for:', searchTerm, 'in list index:', index);
            
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
            var driverId = $('#selectedDriverEdit').val();
            
            if (!driverId) {
                alert('Please select a driver first');
                return;
            }
            
            var index = $(this).data('index');
            var productId = $(this).data('value');
            var productName = $(this).data('name');
            var productCode = $(this).data('code');
            
            $('#editProductDropdown' + index).html(`<i class="fa fa-cube mr-2"></i>${productName} (${productCode})`).attr('title', `${productName} (${productCode})`);
            $(this).closest('tr').find('.edit-product-id-input').val(productId);
            $(this).closest('tr').find('.edit-product-error').text('');
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            
            // Close the dropdown
            $(this).closest('.dropdown').removeClass('show');
            $(this).closest('.dropdown-menu').removeClass('show');
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
                itemsHtml += '<thead class="bg-light"><tr><th width="5%">#</th><th>Product</th><th width="20%" class="text-center">Requested Quantity</th></tr></thead><tbody>';
                
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
                    itemsHtml += '<td class="text-center">' + (index + 1) + '</td>';
                    itemsHtml += '<td>' + productName + '</td>'; 
                    itemsHtml += '<td class="text-center"><strong>' + quantity + '</strong></td>';
                    itemsHtml += '</tr>';
                    
                    totalQuantity += parseInt(quantity);
                });
                
                itemsHtml += '</tbody>';
                itemsHtml += '<tfoot class="bg-light"><tr>';
                itemsHtml += '<td colspan="2" class="text-right"><strong>Total:</strong></td>';
                itemsHtml += '<td class="text-center"><strong>' + totalQuantity + '</strong></td>';
                itemsHtml += '</tr></tfoot>';
                itemsHtml += '</table></div>';
                
                $('#viewItemsTable').html(itemsHtml);
            } else {
                // For backward compatibility with old single-item requests
                var singleItemHtml = '<div class="table-responsive"><table class="table table-sm table-bordered">';
                singleItemHtml += '<thead class="bg-light"><tr><th width="5%">#</th><th>Product</th><th width="20%" class="text-center">Requested Quantity</th></tr></thead><tbody>';
                
                if (requestData.product_id && requestData.quantity) {
                    var productName = getProductName(requestData.product_id);
                    singleItemHtml += '<tr>';
                    singleItemHtml += '<td class="text-center">1</td>';
                    singleItemHtml += '<td>' + (requestData.product_name || productName) + '</td>'; 
                    singleItemHtml += '<td class="text-center"><strong>' + requestData.quantity + '</strong></td>';
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
                    <button type="button" class="btn btn-success mr-2 rounded-0 px-4" id="approveFromViewBtn">
                        <i class="fa fa-check mr-1"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger rounded-0 px-4" id="rejectFromViewBtn">
                        <i class="fa fa-times mr-1"></i> Reject
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
                $('#driverError').text('⚠️ Please select a driver');
                return false;
            }
            
            // Validate items (blocked products check removed since they can't be selected)
            var hasErrors = false;
            var items = [];
            var productIds = new Set();
            
            $('#itemsBody tr').each(function(index) {
                var productId = $(this).find('.product-id-input').val();
                var quantity = $(this).find('.quantity-input').val();
                var productError = $(this).find('.product-error');
                var quantityError = $(this).find('.quantity-error');
                
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
                $('#itemsError').text('⚠️ Please add at least one valid item with product selected and quantity entered');
                hasErrors = true;
            }
            
            if (hasErrors) {
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
        $('#editRequestForm').submit(function(e) {
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
                save_and_approve: $('#saveAndApprove').val(),
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
                        
                        $('#saveAndApprove').val('0');
                        
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
        
        // Helper function to approve request
        function approveRequest(requestId) {
            if (typeof ShowLoad === 'function') ShowLoad();
            
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
                    if (typeof HideLoad === 'function') HideLoad();
                    if (response.success) {
                        showNotification('success', response.message);
                        
                        $('#viewRequest, #rejectReasonModal').modal('hide');
                        
                        if (table && typeof table.ajax !== 'undefined') {
                            table.ajax.reload(null, false);
                        } else if (table && typeof table.draw !== 'undefined') {
                            table.draw(false);
                        } else {
                            location.reload();
                        }
                        updateNotificationBadges();
                    } else {
                        showNotification('error', response.message || 'Failed to approve request');
                    }
                },
                error: function(xhr) {
                    if (typeof HideLoad === 'function') HideLoad();
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

        function updateNotificationBadges() {
            $.ajax({
                url: '{{ route("notification.counts") }}',
                type: 'GET',
                success: function(data) {
                    var stockRequestBadge = $('#stockRequestBadge');
                    if (data.pendingStockRequests > 0) {
                        stockRequestBadge.text(data.pendingStockRequests).show();
                    } else {
                        stockRequestBadge.hide();
                    }
                    
                    var stockCountBadge = $('#stockCountBadge');
                    if (data.pendingStockCounts > 0) {
                        stockCountBadge.text(data.pendingStockCounts).show();
                    } else {
                        stockCountBadge.hide();
                    }
                }
            });
        }

        // Helper function to reject request
        function rejectRequest(requestId, rejectReason) {
            if (typeof ShowLoad === 'function') ShowLoad();
            
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
                    if (typeof HideLoad === 'function') HideLoad();
                    if (response.success) {
                        showNotification('success', response.message);
                        
                        $('#viewRequest, #rejectReasonModal').modal('hide');
                        
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
                    if (typeof HideLoad === 'function') HideLoad();
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

        // ============================================
        // DELETE FUNCTIONALITY
        // ============================================
        
        $(document).on('submit', 'form[action*="destroy"]', function(e) {
            e.preventDefault();
            var form = $(this);
            
            if (confirm('Are you sure you want to delete this inventory request?')) {
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
            $('#editRequestForm')[0].reset();
            $('#selectedDriverEdit').val('');
            $('#dropdownDriverEdit').html('<i class="fa fa-user mr-2"></i>Select Driver');
            $('#driverListEdit .driver-item').removeClass('active');
            $('#editItemsBody').empty();
            editItemCounter = 0;
            $('#editItemsError, #driverEditError').text('');
        });

        $('#viewRequest').on('hidden.bs.modal', function () {
            $('#viewRequestId').text('');
            $('#viewRequestIdText, #viewDriverName, #viewRemarks, #viewCreatedAt, #viewApprovedBy, #viewApprovedAt, #viewRejectedBy, #viewRejectedAt, #viewRejectionReason').text('');
            $('#viewStatusBadge').html('');
            $('#viewActionButtons').html('');
            $('#viewItemsTable').html('');
            currentRequestId = null;
            currentRequestStatus = null;
        });

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