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
            var customers = @json($customers ?? []);
            var selectedCustomers = @json($selectedCustomers ?? []);
            
            // Function to render customers table
            function renderCustomersTable() {
                var tbody = $('#customers-sequence-body');
                tbody.empty();
                
                if (selectedCustomers.length === 0) {
                    // For create mode - add one empty row
                    addEmptyRow();
                } else {
                    // For edit mode - render existing customers
                    $.each(selectedCustomers, function(index, customer) {
                        var rowHtml = `
                            <tr data-sequence="${customer.sequence || (index + 1)}">
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
                        tbody.append(rowHtml);
                    });
                }
                
                // Initialize select2 for all select elements
                $('.customer-select').select2({
                    width: '100%',
                    placeholder: 'Select Customer'
                });
                
                // Make table rows sortable
                makeRowsSortable();
                
                // Update the hidden field
                updateHiddenField();
            }
            
            // Function to add an empty row
            function addEmptyRow() {
                var rowCount = $('#customers-sequence-table tbody tr').length;
                var nextSequence = rowCount + 1;
                
                var rowHtml = `
                    <tr data-sequence="${nextSequence}">
                        <td class="align-middle text-center">${rowCount + 1}</td>
                        <td class="align-middle text-center">
                            <input type="number" 
                                min="1" 
                                class="form-control form-control-sm sequence-input" 
                                value="${nextSequence}"
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
                
                $('#customers-sequence-body').append(rowHtml);
                
                // Initialize select2 for the new row
                $('#customers-sequence-body tr:last .customer-select').select2({
                    width: '100%',
                    placeholder: 'Select Customer'
                });
                
                // Update row numbers
                updateRowNumbers();
            }
            
            // Function to make rows sortable
            function makeRowsSortable() {
                $("#customers-sequence-table tbody").sortable({
                    update: function(event, ui) {
                        updateRowNumbers();
                    }
                }).disableSelection();
            }
            
            // Function to update row numbers
            function updateRowNumbers() {
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
                addEmptyRow();
            });
            
            // Remove customer button (delegated event)
            $(document).on('click', '.remove-customer-btn', function() {
                var rowCount = $('#customers-sequence-table tbody tr').length;
                if (rowCount <= 1) {
                    // If it's the last row, just clear the selection instead of removing
                    $(this).closest('tr').find('.customer-select').val('').trigger('change');
                    $(this).closest('tr').find('.sequence-input').val(1);
                } else {
                    $(this).closest('tr').remove();
                    updateRowNumbers();
                }
                updateHiddenField();
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
                updateHiddenField();
            });
            
            // Form validation and submission
            $('form').submit(function(e) {
                var name = $('#name').val().trim();
                if (!name) {
                    e.preventDefault();
                    alert("{{ __('customer_group.name_required') }}");
                    $('#name').focus();
                    return false;
                }
                
                // Simple check for duplicate customers (only on submit)
                var selectedIds = [];
                var hasDuplicates = false;
                var duplicateCustomers = [];
                
                $('#customers-sequence-table tbody tr').each(function() {
                    var customerId = $(this).find('.customer-select').val();
                    if (customerId) {
                        if (selectedIds.includes(customerId)) {
                            hasDuplicates = true;
                            var customerName = $(this).find('.customer-select option:selected').text();
                            if (!duplicateCustomers.includes(customerName)) {
                                duplicateCustomers.push(customerName);
                            }
                        } else {
                            selectedIds.push(customerId);
                        }
                    }
                });
                
                if (hasDuplicates) {
                    e.preventDefault();
                    alert("Duplicate customers found: " + duplicateCustomers.join(", ") + "\nPlease remove duplicates before submitting.");
                    return false;
                }
                
                // Remove any completely empty rows (no customer selected)
                $('#customers-sequence-table tbody tr').each(function() {
                    if (!$(this).find('.customer-select').val()) {
                        $(this).remove();
                    }
                });
                
                // Update sequence numbers after removing empty rows
                updateRowNumbers();
                
                // Convert JSON to array for form submission
                var customerData = JSON.parse($('#customer-ids-json').val() || '[]');
                
                // Remove any existing hidden inputs
                $('input[name^="customer_ids["]').remove();
                
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
                
                return true;
            });
            
            // Initial render
            renderCustomersTable();
            
            // Ensure at least one row exists
            setTimeout(function() {
                if ($('#customers-sequence-table tbody tr').length === 0) {
                    addEmptyRow();
                }
                
                // Hide the loading spinner
                if (typeof HideLoad === 'function') {
                    HideLoad();
                }
            }, 100);
        });
    </script>
@endpush