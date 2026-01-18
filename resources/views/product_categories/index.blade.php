@extends('layouts.app')

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">{{ __('Product Categories') }}</li>
    </ol>
    <div class="container-fluid">
        <div class="animated fadeIn">
            @include('flash::message')
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-align-justify"></i>
                            {{ __('Product Categories') }}
                        </div>
                        <div class="card-body">
                            @include('product_categories.table')
                            <div class="pull-right mr-3">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Category Modal -->
    <div id="createCategory" class="modal fade">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ __('Create Product Category') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body text-center">
                    {!! Form::open(['route' => 'productCategories.store', 'enctype' => 'multipart/form-data', 'id' => 'createCategoryForm']) !!}
                                        
                        <div class="form-group">
                            <label for="name" class="col-form-label">{{ __('Name') }}:</label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-tag"></i></span>
                                </div>
                                <input type="text" class="form-control" placeholder="Enter category name" name="name" id="nameCreate" required>
                            </div>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label for="status" class="col-form-label">{{ __('Status') }}:</label>
                            <select class="form-control" name="status" id="statusCreate" required>
                                <option value="">{{ __('Select Status') }}</option>
                                <option value="1">{{ __('Active') }}</option>
                                <option value="0">{{ __('Inactive') }}</option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-0">{{ __('Create Category') }}</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategory" class="modal fade">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{ __('Edit Product Category') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body text-center">
                    {!! Form::open(['route' => ['productCategories.update', ':id'], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'id' => 'editCategoryForm']) !!}
                    
                    <!-- Name -->
                    <div class="form-group">
                        <label for="name" class="col-form-label">{{ __('Name') }}:</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-tag"></i></span>
                            </div>
                            <input type="text" class="form-control" placeholder="Enter category name" name="name" id="nameEdit" required>
                        </div>
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label for="status" class="col-form-label">{{ __('Status') }}:</label>
                        <select class="form-control" name="status" id="statusEdit" required>
                            <option value="1">{{ __('Active') }}</option>
                            <option value="0">{{ __('Inactive') }}</option>
                        </select>
                        @error('status')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-0">{{ __('Update Category') }}</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Category Modal -->
    <div id="viewCategory" class="modal fade">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">Product Category Details <span id="viewCategoryId"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th width="30%">Category ID:</th>
                                        <td id="viewCategoryIdText"></td>
                                    </tr>
                                    <tr>
                                        <th>Name:</th>
                                        <td id="viewName"></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td><span id="viewStatusBadge"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Products Count:</th>
                                        <td id="viewProductsCount"></td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        .badge {
            font-size: 85%;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-ghost-primary {
            background-color: transparent;
            color: #007bff;
            border: 1px solid #007bff;
        }
        .btn-ghost-primary:hover {
            background-color: #007bff;
            color: white;
        }
        .btn-ghost-success {
            background-color: transparent;
            color: #28a745;
            border: 1px solid #28a745;
        }
        .btn-ghost-success:hover {
            background-color: #28a745;
            color: white;
        }
        .btn-ghost-danger {
            background-color: transparent;
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        .btn-ghost-danger:hover {
            background-color: #dc3545;
            color: white;
        }
        .btn-group .btn {
            margin-right: 2px;
        }
    </style>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        var table = window.LaravelDataTables["dataTableBuilder"] || $('.data-table').DataTable();
        var currentCategoryId = null;

        // Handle view modal opening
        $(document).on('click', '.view-category-btn', function(e) {
            e.preventDefault();
            var categoryId = $(this).data('id');
            var categoryData = $(this).data('category');
            
            // Parse JSON string if needed
            if (typeof categoryData === 'string') {
                categoryData = JSON.parse(categoryData);
            }
            
            currentCategoryId = categoryId;
            
            // Update modal title with category ID
            $('#viewCategoryId').text('(# ' + categoryId + ')');
            $('#viewCategoryIdText').text(categoryId);
            
            // Fill view modal with data
            $('#viewName').text(categoryData.name);
            $('#viewProductsCount').text(categoryData.products_count || 0);
            
            // Set status with badge
            var status = categoryData.status;
            var badgeClass = status == 1 ? 'badge-success' : 'badge-danger';
            var statusText = status == 1 ? 'Active' : 'Inactive';
            $('#viewStatusBadge').html('<span class="badge ' + badgeClass + '">' + statusText + '</span>');
            
            // Show modal
            $('#viewCategory').modal('show');
        });

        // Handle edit modal opening
        $(document).on('click', '.edit-category-btn', function(e) {
            e.preventDefault();
            var categoryId = $(this).data('id');
            var categoryData = $(this).data('category');
            
            // Parse JSON string if needed
            if (typeof categoryData === 'string') {
                categoryData = JSON.parse(categoryData);
            }
            
            // Update form action URL
            var formAction = '{{ route("productCategories.update", ":id") }}';
            formAction = formAction.replace(':id', categoryId);
            $('#editCategoryForm').attr('action', formAction);
            
            // Fill form with category data
            $('#nameEdit').val(categoryData.name);
            $('#statusEdit').val(categoryData.status);
            
            // Show modal
            $('#editCategory').modal('show');
        });

        // Handle delete action
        $(document).on('submit', 'form[action*="destroy"]', function(e) {
            e.preventDefault();
            var form = $(this);
            
            if (confirm('Are you sure you want to delete this category?')) {
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

        // Handle create form submission
        $('#createCategoryForm').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            
            // Validate required fields
            var name = $('#nameCreate').val();
            var status = $('#statusCreate').val();
            
            if (!name) {
                alert('Please enter a category name');
                return false;
            }
            
            if (status === '') {
                alert('Please select a status');
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
                        $('#createCategory').modal('hide');
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
        $('#editCategoryForm').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            
            // Validate required fields
            var name = $('#nameEdit').val();
            var status = $('#statusEdit').val();
            
            if (!name) {
                alert('Please enter a category name');
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
                        $('#editCategory').modal('hide');
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

        // Clear create modal when closed
        $('#createCategory').on('hidden.bs.modal', function () {
            $('#createCategoryForm')[0].reset();
        });

        // Clear edit modal when closed
        $('#editCategory').on('hidden.bs.modal', function () {
            $('#editCategoryForm')[0].reset();
        });

        // Clear view modal when closed
        $('#viewCategory').on('hidden.bs.modal', function () {
            $('#viewCategoryId').text('');
            $('#viewCategoryIdText, #viewName, #viewProductsCount').text('');
            $('#viewStatusBadge').html('');
            currentCategoryId = null;
        });

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

    // Keyboard shortcut for creating new category
    $(document).keyup(function(e) {
        if(e.altKey && e.keyCode == 78 && ($('#createCategory').length > 0)) {
            $('#createCategory').modal('show');
        }
    });
</script>
@endpush