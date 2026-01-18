<!-- Driver Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('driver_id', 'Agents') !!}<span class="asterisk"> *</span>
    {!! Form::select('driver_id', $drivers, null, ['class' => 'form-control select2-driver', 'placeholder' => 'Pick a Driver...', 'autofocus', 'required', 'id' => 'driver_id']) !!}
</div>

<!-- Customer Group Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('customer_group_id', __('Customer Group')) !!}<span class="asterisk"> *</span>
    {!! Form::select('customer_group_id', $customerGroups, null, ['class' => 'form-control select2-customer-group', 'placeholder' => 'Pick a Customer Group...', 'required', 'id' => 'customer_group_id']) !!}
</div>

<!-- Sequence Field -->
<!-- <div class="form-group col-sm-6">
    {!! Form::label('sequence', __('assign.sequence')) !!}<span class="asterisk"> *</span>
    {!! Form::number('sequence', null, ['class' => 'form-control', 'min' => 1, 'required']) !!}
</div> -->

<!-- Customer Group Details Section -->
<div class="form-group col-sm-12">
    <div class="card" id="customer-group-details-card" style="display: none;">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fa fa-users"></i> {{ __('Customer In this group') }}
                <span class="badge badge-info" id="customer-count-badge">0</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div id="customer-list-container">
                        <div class="text-center text-muted py-3" id="no-customers-message">
                            <i class="fa fa-info-circle fa-2x mb-2"></i>
                            <p>{{ __('Select a customer group to see customers') }}</p>
                        </div>
                        <div id="customers-list" style="display: none;">
                            <!-- Customer list will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fa fa-info-circle"></i> {{ __('Group Info') }}</h6>
                        </div>
                        <div class="card-body">
                            <div id="group-info">
                                <p class="text-muted">{{ __('assign.no_group_selected') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit(__('assign.save'), ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('assigns.index') }}" class="btn btn-secondary">{{ __('assign.cancel') }}</a>
</div>

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da;
            border-radius: .25rem;
            height: 38px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .asterisk {
            color: red;
        }
        .customer-item {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        .customer-item:hover {
            background-color: #f8f9fa;
        }
        .customer-item:last-child {
            border-bottom: none;
        }
        .customer-name {
            font-weight: 500;
            color: #333;
        }
        .customer-id {
            font-size: 0.85em;
            color: #6c757d;
        }
        #customer-group-details-card {
            transition: all 0.3s ease;
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .customer-list-container {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2 for driver field
            $('#driver_id').select2({
                placeholder: "{{ __('Search Agent') }}",
                allowClear: true,
                width: '100%'
            });
            
            // Initialize Select2 for customer group field
            $('#customer_group_id').select2({
                placeholder: "{{ __('Search Customer Group') }}",
                allowClear: true,
                width: '100%'
            });
            
            // When customer group is selected, load customers
            $('#customer_group_id').on('change', function() {
                var customerGroupId = $(this).val();
                if (customerGroupId) {
                    loadCustomerGroupDetails(customerGroupId);
                    $('#customer-group-details-card').slideDown();
                } else {
                    $('#customer-group-details-card').slideUp();
                    resetCustomerGroupDetails();
                }
            });
            
            function loadCustomerGroupDetails(customerGroupId) {
                // Show loading state
                $('#no-customers-message').hide();
                $('#customers-list').html('<div class="text-center py-4"><div class="loading-spinner"></div><p class="mt-2">{{ __("Loading Customers") }}</p></div>').show();
                
                var url = "/assigns/get-group-customers/" + customerGroupId;
                console.log('Loading customer group from URL:', url);

                $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Response received:', response);
                        if (response.status) {
                            displayCustomerGroupDetails(response.data);
                        } else {
                            console.log('Error in response:', response.message);
                            showError("{{ __('Failed to load group') }}: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', {
                            xhr: xhr,
                            status: status,
                            error: error,
                            responseText: xhr.responseText
                        });
                        showError("{{ __('Connection Error') }}: " + error + ". Check console for details.");
                    }
                });
            }
            
            function displayCustomerGroupDetails(data) {
                console.log('Displaying data:', data);
                
                // Update group info
                var groupInfoHtml = '<h6>' + data.name + '</h6>';
                if (data.description) {
                    groupInfoHtml += '<p class="text-muted small">' + data.description + '</p>';
                }
                groupInfoHtml += '<hr class="my-2">';
                if (data.created_at) {
                    groupInfoHtml += '<p class="small"><i class="fa fa-calendar"></i> {{ __("Created") }}: ' + formatDate(data.created_at) + '</p>';
                }
                
                $('#group-info').html(groupInfoHtml);
                
                // Update customer list
                if (data.customers && data.customers.length > 0) {
                    var customersHtml = '';
                    $.each(data.customers, function(index, customer) {
                        customersHtml += '<div class="customer-item">';
                        customersHtml += '<div class="customer-name">' + (index + 1) + ') ' + customer.company + '</div>';
                        customersHtml += '</div>';
                    });

                    
                    $('#customers-list').html(customersHtml);
                    $('#customer-count-badge').text(data.customers.length);
                } else {
                    $('#customers-list').html('<div class="text-center text-muted py-4"><i class="fa fa-user-times fa-2x mb-2"></i><p>{{ __("No customers in group") }}</p></div>');
                    $('#customer-count-badge').text('0');
                }
                
                $('#customers-list').show();
            }
            
            function resetCustomerGroupDetails() {
                $('#no-customers-message').show();
                $('#customers-list').hide();
                $('#group-info').html('<p class="text-muted">{{ __("No Group Selected") }}</p>');
                $('#customer-count-badge').text('0');
            }
            
            function showError(message) {
                $('#customers-list').html('<div class="alert alert-danger">' + message + '</div>').show();
                $('#customer-count-badge').text('0');
                $('#group-info').html('<p class="text-muted">{{ __("Error loading info") }}</p>');
            }
            
            function formatDate(dateString) {
                if (!dateString) return 'N/A';
                var date = new Date(dateString);
                return date.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }
            
            // If editing and customer group is already selected, load details
            @if(isset($assign) && $assign->customer_group_id)
                $('#customer_group_id').trigger('change');
            @endif
            
            // Form validation - Remove sequence validation since you commented it out
            $('form').submit(function(e) {
                var driverId = $('#driver_id').val();
                var customerGroupId = $('#customer_group_id').val();
                
                if (!driverId) {
                    e.preventDefault();
                    alert("{{ __('Driver is required') }}");
                    $('#driver_id').focus();
                    return false;
                }
                
                if (!customerGroupId) {
                    e.preventDefault();
                    alert("{{ __('Customer Group is required') }}");
                    $('#customer_group_id').focus();
                    return false;
                }
                
                return true;
            });
            
            // Keyboard shortcuts
            $(document).keyup(function(e) {
                if (e.key === "Escape") {
                    $('form a.btn-secondary')[0].click();
                }
                if (e.ctrlKey && e.key === "s") {
                    e.preventDefault();
                    $('form').submit();
                }
            });
            
            HideLoad();
        });
    </script>
@endpush