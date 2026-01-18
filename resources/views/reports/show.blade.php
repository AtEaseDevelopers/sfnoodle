@extends('layouts.app')

@section('content')
<ol class="breadcrumb">
    <li class="breadcrumb-item">
        <a href="{{ route('reports.index') }}">{{ __('report.reports') }}</a>
    </li>
    <li class="breadcrumb-item active">{{ $report->name }}</li>
</ol>
<div class="container-fluid">
    <div class="animated fadeIn">
        @include('flash::message')
        @include('coreui-templates::common.errors')
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <strong>{{ $report->name }}</strong>
                        <a href="{{ route('reports.index') }}" class="btn btn-light">Back</a>
                    </div>
                    <div class="card-body">
                        <form method="POST"  target="_blank" action="{{ route('reports.run') }}" accept-charset="UTF-8" id="reportForm">
                            @csrf
                            <input type="hidden" name="report_id" value="{{ $report->id }}">
                            
                            <div class="row">
                                @php
                                    $reportType = $report->sqlvalue;
                                @endphp
                                
                                @if($reportType == 'DAILY_SALES_REPORT')
                                    <!-- Daily Sales Report Form -->
                                    <div class="form-group col-md-4">
                                        <label for="report_date">Report Date: <span class="text-danger">*</span></label>
                                        <input required class="form-control reportdate" id="report_date" name="report_date" type="text" autocomplete="off">
                                        <small class="text-muted">Select the date for the sales report</small>
                                    </div>
                                    
                                @elseif($reportType == 'STOCK_COUNT_REPORT')
                                    <!-- Stock Count Report Form -->
                                    <div class="form-group col-md-4">
                                        <label for="report_date">Report Date: <span class="text-danger">*</span></label>
                                        <input required class="form-control reportdate" id="report_date" name="report_date" type="text" autocomplete="off">
                                        <small class="text-muted">Select the date to filter trips</small>
                                    </div>
                                    
                                    <div class="form-group col-md-4">
                                        <label for="driver_id">Driver:</label>
                                        <select class="form-control" id="driver_id" name="driver_id" disabled>
                                            <option value="">Select a date first</option>
                                        </select>
                                        <small class="text-muted">Drivers will appear after selecting a date</small>
                                    </div>
                                    
                                    <div class="form-group col-md-4">
                                        <label for="trip_id">Trip Number: <span class="text-danger">*</span></label>
                                        <select class="form-control" id="trip_id" name="trip_id" disabled required>
                                            <option value="">Select a driver first</option>
                                        </select>
                                        <small class="text-muted">Trips will appear after selecting a driver</small>
                                    </div>
                                    
                                @else
                                    <!-- Default fallback for other reports -->
                                    <div class="col-12">
                                        <div class="alert alert-warning">
                                            <i class="fa fa-exclamation-triangle"></i> This report type is not configured. Please contact administrator.
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="form-group col-sm-12 mt-4">
                                <button type="submit" class="btn btn-primary" id="runReportBtn" disabled>
                                    <i class="fa fa-play-circle"></i> Run Report
                                </button>
                                <a href="{{ route('reports.index') }}" class="btn btn-light">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .field-disabled {
        background-color: #f8f9fa !important;
        border-color: #e9ecef !important;
        cursor: not-allowed !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        HideLoad();

        // Initialize datepicker
        $('.reportdate').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: true,
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down",
                previous: "fa fa-chevron-left",
                next: "fa fa-chevron-right",
                today: "fa fa-clock-o",
                clear: "fa fa-trash-o"
            },
            sideBySide: true
        });
                
        // Get report type
        const reportType = "{{ $report->sqlvalue }}";
        
        if (reportType === 'STOCK_COUNT_REPORT') {
            setupStockCountReport();
        } else if (reportType === 'DAILY_SALES_REPORT') {
            setupDailySalesReport();
        }

        // Escape key to go back
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                window.location.href = "{{ route('reports.index') }}";
            }
        });

        // Prevent form submission if button is disabled
        $('#reportForm').on('submit', function(e) {
            if ($('#runReportBtn').prop('disabled')) {
                e.preventDefault();
                alert('Please fill all required fields before running the report.');
            }
        });
    });

    function setupDailySalesReport() {
        console.log('Setting up Daily Sales Report');
        
        const dateField = $('#report_date');
        
        // Multiple ways to detect date changes
        dateField.on('change', function() {
            handleDateChange($(this).val());
        });
        
        // Use datetimepicker specific event
        dateField.on('dp.change', function(e) {
            handleDateChange($(this).val());
        });
        
        // Also listen for blur
        dateField.on('blur', function() {
            handleDateChange($(this).val());
        });
        
        function handleDateChange(dateValue) {
            console.log('Date changed to:', dateValue);
            if (dateValue) {
                $('#runReportBtn').prop('disabled', false);
            } else {
                $('#runReportBtn').prop('disabled', true);
            }
        }
    }

    function setupStockCountReport() {
        console.log('Setting up Stock Count Report');
        
        const dateField = $('#report_date');
        const driverField = $('#driver_id');
        const tripField = $('#trip_id');
        const runButton = $('#runReportBtn');
        
        // Track current state
        let selectedDate = '';
        let selectedDriver = '';
        let selectedTrip = '';
        
        // Handle date selection
        dateField.on('dp.change', function(e) {
            const dateValue = $(this).val();
            selectedDate = dateValue;
            
            if (dateValue) {
                // Clear previous selections
                driverField.html('<option value="">Loading drivers...</option>').prop('disabled', true);
                tripField.html('<option value="">Select driver first</option>').prop('disabled', true);
                runButton.prop('disabled', true);
                
                // Fetch drivers for the selected date
                fetchDriversByDate(dateValue);
            } else {
                // Reset if date is cleared
                resetDriverField();
                resetTripField();
                runButton.prop('disabled', true);
            }
        });
        
        // Handle driver selection
        driverField.on('change', function() {
            const driverId = $(this).val();
            selectedDriver = driverId;
            
            if (driverId && selectedDate) {
                // Clear trips
                tripField.html('<option value="">Loading trips...</option>').prop('disabled', true);
                runButton.prop('disabled', true);
                
                // Fetch trips for selected driver and date
                fetchTripsByDriverDate(selectedDate, driverId);
            } else {
                // Reset trips if driver is cleared
                resetTripField();
                runButton.prop('disabled', true);
            }
        });
        
        // Handle trip selection
        tripField.on('change', function() {
            selectedTrip = $(this).val();
            
            // Enable run button only when all fields are selected
            if (selectedDate && selectedDriver && selectedTrip) {
                runButton.prop('disabled', false);
            } else {
                runButton.prop('disabled', true);
            }
        });
        
        // Function to fetch drivers by date
        function fetchDriversByDate(date) {
            $.ajax({
                url: '{{ route("reports.getDriversByDate") }}',
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: { date: date },
                success: function(response) {
                    if (response.success) {
                        if (response.drivers && response.drivers.length > 0) {
                            let options = '<option value="">Select Driver</option>';
                            response.drivers.forEach(function(driver) {
                                options += `<option value="${driver.id}">${driver.name}</option>`;
                            });
                            
                            driverField.html(options).prop('disabled', false);
                        } else {
                            driverField.html('<option value="">No drivers found for this date</option>')
                                .prop('disabled', true)
                                .addClass('field-disabled');
                        }
                    } else {
                        showError('Failed to load drivers: ' + response.message);
                        resetDriverField();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    showError('Error loading drivers. Status: ' + xhr.status);
                    resetDriverField();
                }
            });
        }
        
        // Function to fetch trips by driver and date
        function fetchTripsByDriverDate(date, driverId) {
            $.ajax({
                url: '{{ route("reports.getTripsByDriverDate") }}',
                method: 'GET',
                data: { 
                    date: date,
                    driver_id: driverId 
                },
                success: function(response) {
                    if (response.success) {
                        if (response.trips.length > 0) {
                            let options = '<option value="">Select Trip</option>';
                            response.trips.forEach(function(trip) {
                                // Using UUID as identifier, adjust if needed
                                options += `<option value="${trip.uuid}">${trip.uuid}</option>`;
                            });
                            
                            tripField.html(options).prop('disabled', false).removeClass('field-disabled');
                        } else {
                            tripField.html('<option value="">No trips found for this driver</option>')
                                .prop('disabled', true)
                                .addClass('field-disabled');
                            runButton.prop('disabled', true);
                        }
                    } else {
                        showError('Failed to load trips: ' + response.message);
                        resetTripField();
                    }
                },
                error: function(xhr) {
                    showError('Error loading trips. Please try again.');
                    resetTripField();
                }
            });
        }
        
        // Reset driver field to initial state
        function resetDriverField() {
            driverField.html('<option value="">Select a date first</option>')
                .prop('disabled', true)
                .val('')
                .addClass('field-disabled');
            selectedDriver = '';
        }
        
        // Reset trip field to initial state
        function resetTripField() {
            tripField.html('<option value="">Select a driver first</option>')
                .prop('disabled', true)
                .val('')
                .addClass('field-disabled');
            selectedTrip = '';
        }
        
        // Show error message
        function showError(message) {
            // You can use a toast or alert here
            alert('Error: ' + message);
            console.error(message);
        }
        
        // Initialize with disabled fields
        driverField.addClass('field-disabled');
        tripField.addClass('field-disabled');
    }
</script>
@endpush