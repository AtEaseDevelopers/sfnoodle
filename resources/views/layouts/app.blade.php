<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{config('app.name')}}</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link rel="icon" type="image/png" href="{{config('app.url')}}/logo.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 4.1.1 -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css" integrity="sha512-rBi1cGvEdd3NmSAQhPWId5Nd6QxE8To4ADjM2a6n0BrqQdisZ/RPUlm0YycDzvNL1HHAh1nKZqI0kSbif+5upQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@coreui/coreui@2.1.16/dist/css/coreui.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@icon/coreui-icons-free@1.0.1-alpha.1/coreui-icons-free.css">

    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.1/css/simple-line-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.3.0/css/flag-icon.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.sumoselect/3.4.8/sumoselect.min.css" integrity="sha512-vU7JgiHMfDcQR9wyT/Ye0EAAPJDHchJrouBpS9gfnq3vs4UGGE++HNL3laUYQCoxGLboeFD+EwbZafw7tbsLvg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .asterisk{
            color:red !important;
        }
        tfoot{
            display: table-row-group;
        }
        .table.table-striped.table-bordered tfoot th input{
            width:100%;
            border:none;
        }
        .table.table-striped.table-bordered td{
            padding: 0px !important;
            vertical-align: middle !important;
        }
        .table.table-striped.table-bordered th{
            padding: 0px !important;
        }

        #loading {
            position: fixed;
            display: block;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            text-align: center;
            opacity: 0.7;
            background-color: #fff;
            z-index: 99999;
        }

        #loading-image {
            z-index: 999999;
        }
        .dataTables_filter{
            display: none;
        }
        .dt-button.buttons-columnVisibility.active{
            background-color: lightgray;
        }
        .dt-button.button-page-length.active{
            background-color: lightgray;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.active, .dataTables_wrapper .dataTables_paginate .paginate_button.active:hover{
            color: #333 !important;
            border: 1px solid rgba(0, 0, 0, 0.3);
            background-color: rgba(230, 230, 230, 0.1);
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, rgba(230, 230, 230, 0.1)), color-stop(100%, rgba(0, 0, 0, 0.1)));
            background: -webkit-linear-gradient(top, rgba(230, 230, 230, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
            background: -moz-linear-gradient(top, rgba(230, 230, 230, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
            background: -ms-linear-gradient(top, rgba(230, 230, 230, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
            background: -o-linear-gradient(top, rgba(230, 230, 230, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
            background: linear-gradient(to bottom, rgba(230, 230, 230, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled a{
            cursor: no-drop;
        }
        .dataTables_paginate .pagination{
            margin-bottom: 0px;
        }
        .dataTables_paginate{
            padding-top: 12px !important;
        }

        body{
            font-family: sans-serif;
        }
        /* width */
        ::-webkit-scrollbar {
            width: 5px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            background: #fffaea;
            border-radius: 10px;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .table th {
            position: relative;
        }
        .resizer {
            position: absolute;
            top: 0;
            right: 0;
            width: 5px;
            cursor: col-resize;
            user-select: none;
        }
        .resizer:hover,
        .resizing {
            border-right: 2px solid blue;
        }
        @media only screen and (max-width: 850px) {
            .dt-buttons {
                display: none;
            }
            .dt-buttons a {
                display: block;
            }
        }
        @media only screen and (min-width: 851px) {
            .dt-buttons {
                display: block;
            }
        }
        #dataTableBuilder_wrapper, #dataTableBuilder_do_wrapper, #dataTableBuilder_claim_wrapper, #dataTableBuilder_compound_wrapper, #dataTableBuilder_advance_wrapper, #dataTableBuilder_loan_wrapper, #dataTableBuilder_bonus__wrapper, #dataTableBuilder_pdo_wrapper {
            display: block;
        }
        #dataTableBuilder_wrapper tr, #dataTableBuilder_do_wrapper tr, #dataTableBuilder_claim_wrapper tr, #dataTableBuilder_compound_wrapper tr, #dataTableBuilder_advance_wrapper tr, #dataTableBuilder_loan_wrapper tr, #dataTableBuilder_bonus__wrapper tr, #dataTableBuilder_pdo_wrapper tr{
            white-space: nowrap;
        }
        .SumoSelect .select-all{
            height: 40px;
        }

        .dataTableBuilderDiv{
            overflow-x: auto;
            overflow-y: auto;
            height: calc(100vh - 364px);
            width: 100%;
        }
        .dataTableBuilderDivFull{
            width: 100%;
        }
        thead th{
            position: sticky !important;
            top: 0px !important;
            background-color: white;
            z-index: 100;
        }
        tfoot th{
            position: sticky !important;
            top: 20px !important;
            background-color: white;
            z-index: 100;
        }

        #newreportframe{
            width: 100%;
            height: calc(100vh - 307px);
        }

        #dataTableBuilder_do {
            border-collapse: collapse;
            table-layout: fixed;
        }
        #dataTableBuilder_do td {
            width: 4.762% !important;
            white-space: normal;
        }
        #dataTableBuilder_do th {
            width: 4.762% !important;
            white-space: normal !important;
        }

        #dataTableBuilder_claim {
            border-collapse: collapse;
            table-layout: fixed;
        }
        #dataTableBuilder_claim td {
            width: 10% !important;
            white-space: normal !important;
        }
        #dataTableBuilder_claim th {
            width: 10% !important;
            white-space: normal !important;
        }

        #dataTableBuilder_compound {
            border-collapse: collapse;
            table-layout: fixed;
        }
        #dataTableBuilder_compound td {
            width: 12.5% !important;
            white-space: normal !important;
        }
        #dataTableBuilder_compound th {
            width: 12.5% !important;
            white-space: normal !important;
        }

        #dataTableBuilder_advance {
            border-collapse: collapse;
            table-layout: fixed;
        }
        #dataTableBuilder_advance td {
            width: 12.5% !important;
            white-space: normal !important;
        }
        #dataTableBuilder_advance th {
            width: 12.5% !important;
            white-space: normal !important;
        }

        #dataTableBuilder_loan {
            border-collapse: collapse;
            table-layout: fixed;
        }
        #dataTableBuilder_loan td {
            width: 25% !important;
            white-space: normal !important;
        }
        #dataTableBuilder_loan th {
            width: 25% !important;
            white-space: normal !important;
        }

        #dataTableBuilder_bonus {
            border-collapse: collapse;
            table-layout: fixed;
        }
        #dataTableBuilder_bonus td {
            width: 10% !important;
            white-space: normal !important;
        }
        #dataTableBuilder_bonus th {
            width: 10% !important;
            white-space: normal !important;
        }

        #dataTableBuilder_pdo {
            border-collapse: collapse;
            table-layout: fixed;
        }
        #dataTableBuilder_pdo td {
            width: 4.762% !important;
            white-space: normal !important;
        }
        #dataTableBuilder_pdo th {
            width: 4.762% !important;
            white-space: normal !important;
        }
        
        .truncate {
            max-width:150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        #map {
            height: 400px;
        }
        .marker-position {
            bottom: 27px;
            left: 0;
            position: relative;
            background-color: white;
        }

        @media screen and (max-width: 520px) {
            li.paginate_button.previous {
                display: inline !important;
            }

            li.paginate_button.next {
                display: inline !important;
            }

            li.paginate_button {
                display: none !important;
            }
        }

        /* Notification Styles */
        .notification-dropdown .dropdown-menu {
            max-height: 400px;
            overflow-y: auto;
            width: 350px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .notification-badge {
            position: absolute;
            top: 3px;
            right: 8px;
            font-size: 10px;
            padding: 2px 5px;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-item {
            padding: 12px 15px;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            border-bottom: 1px solid #f5f5f5;
            display: block;
            color: #333;
            text-decoration: none;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item.unread {
            border-left-color: #007bff;
            background-color: rgba(0, 123, 255, 0.03);
        }

        .notification-item:hover {
            background-color: #f8f9fa;
            text-decoration: none;
            border-left-color: #0056b3;
        }

        .notification-content {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
        }

        .notification-type {
            font-size: 12px;
            font-weight: 600;
        }

        .notification-time {
            font-size: 11px;
            color: #6c757d;
            white-space: nowrap;
            margin-left: 8px;
        }
        
        .notification-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 2px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-message {
            font-size: 12px;
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .notification-time {
            font-size: 11px;
            text-align: right;
        }

        .badge-sm {
            font-size: 8px;
            padding: 1px 4px;
        }

        .dropdown-footer {
            padding: 8px;
            border-top: 1px solid #dee2e6;
            text-align: center;
        }

        .dropdown-footer a {
            color: #007bff;
            text-decoration: none;
        }

        .dropdown-footer a:hover {
            text-decoration: underline;
        }

        /* ADD MISSING MOBILE RESPONSIVENESS */
        @media (max-width: 576px) {
            .notification-dropdown .dropdown-menu {
                width: 300px;
                max-width: calc(100vw - 30px);
                right: -10px;
                left: auto;
            }
            
            .notification-item {
                padding: 10px 12px;
            }
            
            .notification-title {
                font-size: 13px;
            }
            
            .notification-message {
                font-size: 11px;
            }
        }

        /* ADD MISSING SCROLLBAR STYLES */
        .notification-dropdown .dropdown-menu::-webkit-scrollbar {
            width: 4px;
        }

        .notification-dropdown .dropdown-menu::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 2px;
        }

        .notification-dropdown .dropdown-menu::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 2px;
        }

        .notification-dropdown .dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* ADD MISSING ANIMATION */
        @keyframes fadeInNotification {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-item {
            animation: fadeInNotification 0.3s ease;
        }

        /* ADD MISSING MOBILE BADGE ADJUSTMENT */
        @media (max-width: 768px) {
            .notification-badge {
                top: 2px;
                right: 5px;
                font-size: 9px;
                padding: 1px 4px;
                min-width: 16px;
                height: 16px;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="app header-fixed sidebar-fixed aside-menu-fixed sidebar-lg-show">
    <div id="loading" class="d-flex align-items-center justify-content-center">
        <div id='loading-image' class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
<header class="app-header navbar">
    <button class="navbar-toggler sidebar-toggler d-lg-none mr-auto" type="button" data-toggle="sidebar-show">
        <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="{{config('app.url')}}">
        <img class="navbar-brand-full" src="{{config('app.url')}}/logo.png" height="30"
             alt="Multiline Logo">
        <img class="navbar-brand-minimized" src="{{config('app.url')}}/logo.png" width="30"
             height="30" alt="Multiline Logo">
    </a>
    <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button" data-toggle="sidebar-lg-show">
        <span class="navbar-toggler-icon"></span>
    </button>

    <ul class="nav navbar-nav ml-auto">
    {{-- Notification Icon --}}
        <li class="nav-item dropdown notification-dropdown">
            <a class="nav-link" style="margin-right: 15px" data-toggle="dropdown" href="#" role="button"
            aria-haspopup="true" aria-expanded="false" id="notification-icon">
                <i class="fa fa-bell"></i>
                @php
                    $unreadCount = $unreadCount ?? 0;
                    $notifications = $notifications ?? collect([]);
                @endphp
                @if($unreadCount > 0)
                    <span class="badge badge-danger notification-badge">{{ $unreadCount }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-right notification-dropdown-menu" style="width: 350px;">
                <div class="dropdown-header text-center">
                    <strong>Notifications</strong>
                </div>
                <div class="notification-list">
                    @forelse($notifications as $notification)
                        @php
                            // Determine the notification type and URL
                            $url = '#';
                            $onclick = '';
                            $target = '_blank';
                            
                            if ($notification->inventory_request_id) {
                                // Inventory Request Notification
                                $onclick = "event.preventDefault(); viewInventoryRequest({$notification->inventory_request_id}, {$notification->id});";
                                $url = route('inventoryRequests.index');
                                $target = '';
                            } elseif ($notification->inventory_count_id) {
                                // Inventory Count Notification
                                $onclick = "event.preventDefault(); viewInventoryCount({$notification->inventory_count_id}, {$notification->id});";
                                $url = route('inventoryCounts.index');
                                $target = '';
                            } elseif ($notification->trip_id) {
                                // Trip Report Notification
                                $tripId = $notification->trip->uuid ?? null;
                                if ($tripId) {
                                    $url = route('tripsummaries', $tripId) . '?notification_id=' . $notification->id;
                                }
                            }
                            
                            // Determine notification icon/type label
                            $notificationType = '';
                            if ($notification->inventory_request_id) {
                                $notificationType = '<span class="badge badge-info badge-sm">Inventory Request</span>';
                            } elseif ($notification->inventory_count_id) {
                                $notificationType = '<span class="badge badge-warning badge-sm">Stock Count</span>';
                            } elseif ($notification->trip_id) {
                                $notificationType = '<span class="badge badge-primary badge-sm">Trip Report</span>';
                            }
                        @endphp
                        
                        <a href="{{ $url }}" 
                            @if($onclick) onclick="{{ $onclick }}" @endif
                            @if($target) target="{{ $target }}" @endif
                            class="dropdown-item notification-item {{ !$notification->is_read ? 'unread' : '' }}">
                           <div class="notification-content">
                                <div class="notification-header">
                                    <span class="notification-time">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>    
                                    <span class="notification-type">
                                        {!! $notificationType !!}
                                    </span>
                                </div>
                                <div class="notification-title">
                                    {{ $notification->title }}
                                    @if(!$notification->is_read)
                                        <span class="badge badge-success badge-sm">New</span>
                                    @endif
                                </div>
                                <div class="notification-message">
                                    {{ $notification->message }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="dropdown-item text-center text-muted">
                            No notifications
                        </div>
                    @endforelse
                </div>
                <div class="dropdown-footer text-center">
                    <a href="#" class="mark-all-read-btn small">Mark all as read</a>
                </div>
            </div>
        </li>
        
        {{-- User Profile Dropdown --}}
        <li class="nav-item dropdown">
            <a class="nav-link" style="margin-right: 10px" data-toggle="dropdown" href="#" role="button"
            aria-haspopup="true" aria-expanded="false">
                {{ Auth::user()->name ?? 'User' }}
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-header text-center">
                    <strong>Account</strong>
                </div>
                <a href="{{ url('/logout') }}" class="dropdown-item btn btn-default btn-flat"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fa fa-lock"></i>Logout
                </a>
                <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
</header>

<div class="app-body">
    @include('layouts.sidebar')
    <main class="main">
        @yield('content')
    </main>
</div>
<footer class="app-footer">
    <div>
        <a href="{{config('app.url')}}">{{config('app.name')}} </a>
        <span>&copy; {{date("Y")}} {{config('app.name')}}</span>
    </div>
    <div class="ml-auto">
        <span>Powered by</span>
        <a href="{{config('app.url')}}">{{config('app.name')}}</a>
    </div>
</footer>
</body>
<!-- jQuery 3.1.1 -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js" integrity="sha512-mh+AjlD3nxImTUGisMpHXW03gE6F4WdQyvuFRkjecwuWLwD2yCijw4tKA3NsEFpA1C3neiKhGXPSIGSfCYPMlQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@2.1.16/dist/js/coreui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js" integrity="sha512-yDlE7vpGDP7o2eftkCiPZ+yuUyEcaBwoJoIhdXv71KZWugFqEphIS3PU60lEkFaz8RxaVsMpSvQxMBaKVwA5xg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/df-number-format/2.1.6/jquery.number.min.js" integrity="sha512-3z5bMAV+N1OaSH+65z+E0YCCEzU8fycphTBaOWkvunH9EtfahAlcJqAVN2evyg0m7ipaACKoVk6S9H2mEewJWA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" integrity="sha512-ElRFoEQdI5Ht6kZvyzXhYG9NqjtkmlkfYk0wr6wHxU9JEHakS7UJZNeml5ALk+8IKlU6jDgMabC3vkumRokgJA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.sumoselect/3.4.8/jquery.sumoselect.min.js" integrity="sha512-Ut8/+LO2wW6HfMEz1vxHpiwMMQfw7Yf/0PdpTERAbK2VJQt4eVDsmFL269zUCkeG/QcEcc/tcORSrGHlP89nBQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.1.0/chartjs-plugin-datalabels.min.js" integrity="sha512-Tfw6etYMUhL4RTki37niav99C6OHwMDB2iBT5S5piyHO+ltK2YX8Hjy9TXxhE1Gm/TmAV0uaykSpnHKFIAif/A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<script src="{{config('app.url')}}/resizableColumns.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.0.272/jspdf.debug.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script async
    src="https://maps.googleapis.com/maps/api/js?key={{ config('app.google_api') }}">
</script>

<script>
    function HideLoad(){
        $('#loading').removeClass('d-flex');
        $('#loading').hide();
    }
    
    function ShowLoad(){
        $('#loading').addClass('d-flex');
        $('#loading').show();
    }
    
    function noti(tp,tt,mg){
        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": false,
            "progressBar": false,
            "positionClass": "toast-bottom-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
        
        if(tp == 'e'){
            toastr.error(mg, tt);
        }else if(tp=='i'){
            toastr.info(mg, tt);
        }else if(tp=='w'){
            toastr.warning(mg, tt);
        }else if(tp=='s'){
            toastr.success(mg, tt);
        }
    }
    
    var resize = 1;
    $(document).ready(function() {
        $('.fa.fa-align-justify').on('click', function(event) {
            $('.dt-buttons').toggle();
        });
        if($("#dataTableBuilder").length == 1){
            $.fn.dataTable.ext.errMode = 'none';
        }
    });

    // Notification Functions
    function markNotificationAsRead(notificationId) {
        fetch(`/notifications/${notificationId}/mark-as-read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({})
        }).then(response => {
            if (!response.ok) {
                console.error('Failed to mark notification as read');
            }
            // Remove the 'unread' class from the clicked notification
            const notificationItem = document.querySelector(`a[href*="notification_id=${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.remove('unread');
                notificationItem.querySelector('.badge-success')?.remove();
                
                // Update badge count
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    const currentCount = parseInt(badge.textContent);
                    if (currentCount > 1) {
                        badge.textContent = currentCount - 1;
                    } else {
                        badge.remove();
                    }
                }
            }
        }).catch(error => {
            console.error('Error:', error);
        });
    }

    function viewInventoryRequest(requestId, notificationId) {
        // Mark notification as read
        if (notificationId) {
            markNotificationAsRead(notificationId);
        }
        
        // Redirect to inventory requests page with query parameter
        window.location.href = '{{ route("inventoryRequests.index") }}?view_request=' + requestId;
    }

    function viewInventoryCount(countId, notificationId) {
        // Mark notification as read
        if (notificationId) {
            markNotificationAsRead(notificationId);
        }
        
        // Redirect to inventory counts page with query parameter
        window.location.href = '{{ route("inventoryCounts.index") }}?view_count=' + countId;
    }

    class NotificationManager {
        constructor() {
            this.pollingInterval = null;
            this.pollingDelay = 30000; // 30 seconds
            this.init();
        }

        init() {
            this.startPolling();
            this.bindEvents();
        }

        bindEvents() {
            // Add click handler for mark all as read
            const markAllReadBtn = document.querySelector('.mark-all-read-btn');
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.markAllAsRead();
                });
            }
        }

        startPolling() {
            this.pollingInterval = setInterval(() => {
                this.checkNewNotifications();
            }, this.pollingDelay);
        }

        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        }

        async checkNewNotifications() {
            try {
                const response = await fetch('/notifications/unread-count');
                const data = await response.json();
                
                if (data.success) {
                    this.updateNotificationBadge(data.unread_count);
                }
            } catch (error) {
                console.error('Error checking notifications:', error);
            }
        }

        updateNotificationBadge(count) {
            const badge = document.querySelector('.notification-badge');
            const currentCount = parseInt(badge?.textContent || 0);
            
            if (count > currentCount && count > 0) {
                // Show toast for new notification
                this.showNewNotificationToast(count - currentCount);
            }
            
            if (count > 0) {
                if (badge) {
                    badge.textContent = count;
                } else {
                    this.createBadge(count);
                }
            } else {
                this.removeBadge();
            }
        }

        createBadge(count) {
            const icon = document.querySelector('#notification-icon i');
            if (icon) {
                const badge = document.createElement('span');
                badge.className = 'badge badge-danger notification-badge';
                badge.textContent = count;
                icon.parentNode.appendChild(badge);
            }
        }

        removeBadge() {
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                badge.remove();
            }
        }

        showNewNotificationToast(newCount) {
            if (typeof toastr !== 'undefined') {
                toastr.info(`You have ${newCount} new notification${newCount > 1 ? 's' : ''}!`, 'New Notifications');
            }
        }

        async markAllAsRead() {
            try {
                const response = await fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({})
                });
                
                if (response.ok) {
                    // Remove all unread styles
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                        item.querySelector('.badge-success')?.remove();
                    });
                    
                    // Remove badge
                    this.removeBadge();
                    
                    // Show success message
                    noti('s', 'Success', 'All notifications marked as read');
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
                noti('e', 'Error', 'Failed to mark all notifications as read');
            }
        }
    }

     // ========== DATA TABLE INITIALIZATION HOOK ==========
    
    // Override DataTable initialization to hook into it
    const originalDataTableInit = $.fn.dataTable;
    $.fn.dataTable = function (settings) {
        const table = originalDataTableInit.apply(this, arguments);
        
        // Check if this is initialization (not a method call)
        if (typeof settings === 'object') {
            // Hook into the draw event
            if (table && typeof table.on === 'function') {
                table.on('draw', function () {
                    console.log('DataTable draw event fired');
                    
                    // Check for pending modal requests after each draw
                    setTimeout(checkForPendingModalRequests, 500);
                });
            }
        }
        
        return table;
    };


    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        window.notificationManager = new NotificationManager();
        
        setTimeout(checkForPendingModalRequests, 1000);
        
        // Also check when DataTable is initialized (if any)
        if (typeof $.fn.DataTable !== 'undefined') {
            $(document).on('init.dt', function (e, settings) {
                if (e.namespace === 'dt') {
                    console.log('DataTable initialized');
                    setTimeout(checkForPendingModalRequests, 1500);
                }
            });
        }

    });
</script>
@stack('scripts')
</html>