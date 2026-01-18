<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', __('Name')) !!}<span class="asterisk"> *</span>
    {!! Form::text('name', null, ['class' => 'form-control', 'autofocus', 'required']) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-6">
    {!! Form::label('description', __('customer_group.description')) !!}
    {!! Form::text('description', null, ['class' => 'form-control']) !!}
</div>

<!-- Customers with Sequence Field -->
<div class="form-group col-sm-12">
    {!! Form::label('customer_ids', 'Customer Group (with Sequence)') !!}
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> Drag and drop customers to set their sequence order.
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered" id="customers-sequence-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="5%">Sequence</th>
                    <th width="60%">Customer</th>
                    <th width="15%">Actions</th>
                </tr>
            </thead>
            <tbody id="customers-sequence-body">
                <!-- Will be populated by JavaScript -->
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">
                        <button type="button" class="btn btn-success btn-sm" id="add-customer-btn">
                            <i class="fa fa-plus"></i> Add Customer
                        </button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <!-- Hidden field to store the JSON data -->
    <input type="hidden" name="customer_ids_json" id="customer-ids-json" value="">
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit(__('customer_group.save'), ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('customer_group.index') }}" class="btn btn-secondary">{{ __('customer_group.cancel') }}</a>
</div>

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            min-height: 38px;
            border: 1px solid #ced4da;
        }
        .asterisk {
            color: red;
        }
        #customers-sequence-table tbody {
            cursor: move;
        }
        #customers-sequence-table tr.ui-sortable-helper {
            background-color: #f8f9fa;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .sequence-input {
            width: 60px;
            text-align: center;
        }
        .customer-select {
            width: 100%;
        }
        .duplicate-error {
            border-color: #dc3545 !important;
        }
        .duplicate-error-text {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize customers data
            var customers = @json($customers);
            var selectedCustomers = @json($selectedCustomers ?? []);
            var customerCounter = 0;
            
            // Function to get all currently selected customer IDs
            function getSelectedCustomerIds() {
                var selectedIds = [];
                $('#customers-sequence-table tbody tr').each(function() {
                    var customerId = $(this).find('.customer-select').val();
                    if (customerId) {
                        selectedIds.push(parseInt(customerId));
                    }
                });
                return selectedIds;
            }
            
            // Function to update select2 options based on selected customers
            function updateSelect2Options() {
                var selectedIds = getSelectedCustomerIds();
                
                $('.customer-select').each(function() {
                    var currentSelect = $(this);
                    var currentValue = currentSelect.val();
                    
                    // Store current value to restore after updating options
                    var currentSelectedValue = currentValue;
                    
                    // Clear current options
                    currentSelect.empty();
                    currentSelect.append('<option value="">Select Customer</option>');
                    
                    // Add available customers (not already selected in other rows)
                    $.each(customers, function(id, name) {
                        // If this customer is not selected in ANY row, OR it's the current row's selection
                        if (!selectedIds.includes(parseInt(id)) || id == currentSelectedValue) {
                            var option = $('<option>', {
                                value: id,
                                text: name
                            });
                            
                            // Mark as selected if it's the current value
                            if (id == currentSelectedValue) {
                                option.attr('selected', true);
                            }
                            
                            currentSelect.append(option);
                        }
                    });
                    
                    // Re-initialize select2
                    currentSelect.select2('destroy').select2({
                        width: '100%',
                        placeholder: 'Select Customer'
                    });
                    
                    // Restore value if it was set
                    if (currentSelectedValue) {
                        currentSelect.val(currentSelectedValue).trigger('change');
                    }
                    
                    // Validate for duplicates
                    validateCustomerSelections();
                });
            }
            
            // Function to validate for duplicate selections
            function validateCustomerSelections() {
                var selectedIds = [];
                var hasDuplicates = false;
                
                // Clear previous error states
                $('.customer-select').removeClass('duplicate-error');
                $('.duplicate-error-text').remove();
                
                // Check for duplicates
                $('#customers-sequence-table tbody tr').each(function() {
                    var customerId = $(this).find('.customer-select').val();
                    if (customerId) {
                        if (selectedIds.includes(customerId)) {
                            // Found a duplicate
                            hasDuplicates = true;
                            $(this).find('.customer-select').addClass('duplicate-error');
                            
                            // Add error message if not already present
                            if (!$(this).find('.duplicate-error-text').length) {
                                $(this).find('td').eq(2).append(
                                    '<div class="duplicate-error-text">This customer is already selected in another row</div>'
                                );
                            }
                        } else {
                            selectedIds.push(customerId);
                            $(this).find('.duplicate-error-text').remove();
                        }
                    }
                });
                
                return hasDuplicates;
            }
            
            // Function to render customers table
            function renderCustomersTable() {
                var tbody = $('#customers-sequence-body');
                tbody.empty();
                
                if (selectedCustomers.length === 0) {
                    tbody.html('<tr><td colspan="4" class="text-center">No customers added</td></tr>');
                    updateHiddenField();
                    return;
                }
                
                $.each(selectedCustomers, function(index, customer) {
                    var row = `
                        <tr data-id="${customer.id}" data-sequence="${customer.sequence || (index + 1)}">
                            <td class="align-middle text-center">${index + 1}</td>
                            <td class="align-middle text-center">
                                <input type="number" 
                                       min="1" 
                                       class="form-control form-control-sm sequence-input" 
                                       value="${customer.sequence || (index + 1)}"
                                       style="width: 60px;">
                            </td>
                            <td class="align-middle">
                                <select class="form-control form-control-sm customer-select">
                                    <option value="">Select Customer</option>
                                    ${Object.entries(customers).map(([id, name]) => 
                                        `<option value="${id}" ${id == customer.id ? 'selected' : ''}>${name}</option>`
                                    ).join('')}
                                </select>
                            </td>
                            <td class="align-middle text-center">
                                <button type="button" class="btn btn-danger btn-sm remove-customer-btn">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
                
                // Initialize select2 for customer selects and update options
                updateSelect2Options();
                
                // Make table rows sortable
                $("#customers-sequence-table tbody").sortable({
                    update: function(event, ui) {
                        updateSequenceNumbers();
                    }
                }).disableSelection();
                
                updateHiddenField();
                validateCustomerSelections();
            }
            
            // Function to update sequence numbers
            function updateSequenceNumbers() {
                $('#customers-sequence-table tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                    $(this).find('.sequence-input').val(index + 1);
                    $(this).attr('data-sequence', index + 1);
                });
                updateHiddenField();
            }
            
            // Function to update the hidden JSON field
            function updateHiddenField() {
                var customerData = [];
                
                $('#customers-sequence-table tbody tr').each(function() {
                    var customerId = $(this).find('.customer-select').val();
                    var sequence = $(this).find('.sequence-input').val();
                    
                    if (customerId) {
                        customerData.push({
                            id: parseInt(customerId),
                            sequence: parseInt(sequence) || 1
                        });
                    }
                });
                
                // Sort by sequence
                customerData.sort(function(a, b) {
                    return a.sequence - b.sequence;
                });
                
                $('#customer-ids-json').val(JSON.stringify(customerData));
            }
            
            // Add customer button
            $('#add-customer-btn').click(function() {
                // Find next available sequence
                var maxSequence = 0;
                $('#customers-sequence-table tbody tr').each(function() {
                    var seq = parseInt($(this).find('.sequence-input').val());
                    if (seq > maxSequence) maxSequence = seq;
                });
                
                var newRow = `
                    <tr data-sequence="${maxSequence + 1}">
                        <td class="align-middle text-center">${$('#customers-sequence-table tbody tr').length + 1}</td>
                        <td class="align-middle text-center">
                            <input type="number" 
                                   min="1" 
                                   class="form-control form-control-sm sequence-input" 
                                   value="${maxSequence + 1}"
                                   style="width: 60px;">
                        </td>
                        <td class="align-middle">
                            <select class="form-control form-control-sm customer-select">
                                <option value="">Select Customer</option>
                                ${Object.entries(customers).map(([id, name]) => 
                                    `<option value="${id}">${name}</option>`
                                ).join('')}
                            </select>
                        </td>
                        <td class="align-middle text-center">
                            <button type="button" class="btn btn-danger btn-sm remove-customer-btn">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                
                $('#customers-sequence-body').append(newRow);
                
                // Update select2 options for all selects
                updateSelect2Options();
                
                updateSequenceNumbers();
            });
            
            // Remove customer button (delegated event)
            $(document).on('click', '.remove-customer-btn', function() {
                $(this).closest('tr').remove();
                updateSequenceNumbers();
                updateSelect2Options(); // Update options after removal
            });
            
            // Update sequence when input changes
            $(document).on('change', '.sequence-input', function() {
                var newSequence = parseInt($(this).val());
                if (newSequence < 1) {
                    $(this).val(1);
                    newSequence = 1;
                }
                $(this).closest('tr').attr('data-sequence', newSequence);
                updateHiddenField();
            });
            
            // Update when customer selection changes
            $(document).on('change', '.customer-select', function() {
                updateSelect2Options(); // Update all select2 options
                updateHiddenField();
                validateCustomerSelections();
            });
            
            // Form validation
            $('form').submit(function(e) {
                var name = $('#name').val().trim();
                if (!name) {
                    e.preventDefault();
                    alert("{{ __('customer_group.name_required') }}");
                    $('#name').focus();
                    return false;
                }
                
                // Check for duplicate customers
                if (validateCustomerSelections()) {
                    e.preventDefault();
                    alert("Please remove duplicate customer selections before submitting.");
                    return false;
                }
                
                // Check for empty customer selections
                var hasEmptySelections = false;
                $('#customers-sequence-table tbody tr').each(function() {
                    if (!$(this).find('.customer-select').val()) {
                        hasEmptySelections = true;
                        $(this).find('.customer-select').addClass('duplicate-error');
                    }
                });
                
                if (hasEmptySelections) {
                    e.preventDefault();
                    alert("Please select a customer for all rows.");
                    return false;
                }
                
                // Convert JSON to array for form submission
                var customerData = JSON.parse($('#customer-ids-json').val() || '[]');
                if (customerData.length > 0) {
                    // Add hidden inputs for each customer
                    $.each(customerData, function(index, customer) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'customer_ids[' + index + '][id]',
                            value: customer.id
                        }).appendTo('form');
                        
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'customer_ids[' + index + '][sequence]',
                            value: customer.sequence
                        }).appendTo('form');
                    });
                }
                
                return true;
            });
            
            // Initial render
            renderCustomersTable();
            
            HideLoad();
        });
    </script>
@endpush