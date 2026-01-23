{{-- @canany(['deliveryorder'])
<li class="nav-item {{ Request::is('deliveryOrders*') ? 'active' : '' }}">
    <a class="nav-link {{ Request::is('deliveryOrders*') ? 'active' : '' }}" href="{{ route('deliveryOrders.index') }}">
        <i class="nav-icon icon-notebook"></i>
        <span>{{ trans('side_menu.delivery_orders') }}</span>
    </a>
</li>
@endcanany

@canany(['loan','loanpayment'])
<li class="nav-item nav-dropdown {{ Request::is('loans*','loanpayments*') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-diamond"></i>
        <span>{{ trans('side_menu.loan_management') }}</span>
    </a>

    @can('loan')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('loans*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('loans*') ? 'active' : '' }}" href="{{ route('loans.index') }}">
                    <span>{{ trans('side_menu.loans') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('loanpayment')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('loanpayments*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('loanpayments*') ? 'active' : '' }}" href="{{ route('loanpayments.index') }}">
                    <span>{{ trans('side_menu.loan_payments') }}</span>
                </a>
            </li>
        </ul>
    @endcan

</li>
@endcanany

@canany(['bonus','claim','advance','compound','paymentdetail'])
<li class="nav-item nav-dropdown {{ Request::is('bonuses*','claims*','advances*','compounds*','paymentdetails*') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-wallet"></i>
        <span>{{ trans('side_menu.finance') }}</span>
    </a>

    @can('paymentdetail')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('paymentdetails*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('paymentdetails*') ? 'active' : '' }}" href="{{ route('paymentdetails.index') }}">
                    <span>{{ trans('side_menu.payment_details') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('compound')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('compounds*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('compounds*') ? 'active' : '' }}" href="{{ route('compounds.index') }}">
                    <span>{{ trans('side_menu.compounds') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('advance')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('advances*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('advances*') ? 'active' : '' }}" href="{{ route('advances.index') }}">
                    <span>{{ trans('side_menu.advances') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('claim')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('claims*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('claims*') ? 'active' : '' }}" href="{{ route('claims.index') }}">
                    <span>{{ trans('side_menu.claims') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('bonus')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('bonuses*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('bonuses*') ? 'active' : '' }}" href="{{ route('bonuses.index') }}">
                    <span>{{ trans('side_menu.bonuses') }}</span>
                </a>
            </li>
        </ul>
    @endcan

</li>
@endcanany --}}

@canany(['sales_invoices'])
<li class="nav-item nav-dropdown {{ Request::is('salesInvoices*','salesInvoiceDetails*') ? 'open' : '' }}">
    <a class="nav-link {{ Request::is('salesInvoices*') ? 'active' : '' }}" href="{{ route('salesInvoices.index') }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-receipt" viewBox="0 0 16 16">
        <path d="M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .801.13l.5 1A.5.5 0 0 1 15 2v12a.5.5 0 0 1-.053.224l-.5 1a.5.5 0 0 1-.8.13L13 14.707l-.646.647a.5.5 0 0 1-.708 0L11 14.707l-.646.647a.5.5 0 0 1-.708 0L9 14.707l-.646.647a.5.5 0 0 1-.708 0L7 14.707l-.646.647a.5.5 0 0 1-.708 0L5 14.707l-.646.647a.5.5 0 0 1-.708 0L3 14.707l-.646.647a.5.5 0 0 1-.801-.13l-.5-1A.5.5 0 0 1 1 14V2a.5.5 0 0 1 .053-.224l.5-1a.5.5 0 0 1 .367-.27m.217 1.338L2 2.118v11.764l.137.274.51-.51a.5.5 0 0 1 .707 0l.646.647.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.509.509.137-.274V2.118l-.137-.274-.51.51a.5.5 0 0 1-.707 0L12 1.707l-.646.647a.5.5 0 0 1-.708 0L10 1.707l-.646.647a.5.5 0 0 1-.708 0L8 1.707l-.646.647a.5.5 0 0 1-.708 0L6 1.707l-.646.647a.5.5 0 0 1-.708 0L4 1.707l-.646.647a.5.5 0 0 1-.708 0z"/>
        <path d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5"/>
        </svg>&nbsp;&nbsp;&nbsp;
        <span>{{ trans('Sales Order') }}</span>
    </a>
</li>
@endcanany


@canany(['invoice'])
<li class="nav-item nav-dropdown {{ Request::is('invoices*','invoiceDetails*','invoicePayments') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-notebook"></i>
        <span>{{ trans('side_menu.invoices') }}</span>
    </a>

    @can('invoice')

        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('invoices*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('invoices*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                    <span>{{ trans('side_menu.invoices') }}</span>
                </a>
            </li>
        </ul>
        <!-- <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('invoiceDetails*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('invoiceDetails*') ? 'active' : '' }}" href="{{ route('invoiceDetails.index') }}">
                    <span>{{ trans('side_menu.invoice_details') }}</span>
                </a>
            </li>
        </ul> -->
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('invoicePayments*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('invoicePayments*') ? 'active' : '' }}" href="{{ route('invoicePayments.index') }}">
                    <span>{{ trans('side_menu.payments') }}</span>
                </a>
            </li>
        </ul>
    @endcan

</li>
@endcanany


<!-- @canany(['task'])
<li class="nav-item {{ Request::is('tasks*','taskTransfers*') ? 'open' : '' }}">
    <a class="nav-link {{ Request::is('tasks*','taskTransfers*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">
        <i class="nav-icon icon-menu"></i>
        <span>{{ trans('side_menu.tasks') }}</span>
    </a>
</li>
@endcanany -->

@canany(['trip'])
<li class="nav-item {{ Request::is('trips*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('trips.index') }}">
        <i class="nav-icon icon-login"></i>
        <span>{{ trans('side_menu.trips') }}</span>
    </a>
</li>
@endcanany

<!-- @canany(['checkin'])
<li class="nav-item {{ Request::is('checkin*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('checkins.index') }}">
        <i class="nav-icon icon-check"></i>
        <span>{{ trans('Check In') }}</span>
    </a>
</li>
@endcanany -->

@canany(['inventorybalance','inventorytransaction'])
<li class="nav-item nav-dropdown {{ Request::is('inventoryBalances*','inventoryTransactions*') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-drawer"></i>
        <span>{{ trans('side_menu.inventory') }}</span>
    </a>
    @can('stockrequest')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('inventoryRequests*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('inventoryRequests*') ? 'active' : '' }}" href="{{ route('inventoryRequests.index') }}">
                    <span>{{ trans('Stock Requests') }}</span>
                </a>
            </li>
        </ul>
    @endcan
    @can('stockreturn')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('inventoryReturns*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('inventoryReturns*') ? 'active' : '' }}" href="{{ route('inventoryReturns.index') }}">
                    <span>{{ trans('Stock Return') }}</span>
                </a>
            </li>
        </ul>
    @endcan
    @can('stockcount')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('inventoryCounts*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('inventoryCounts*') ? 'active' : '' }}" href="{{ route('inventoryCounts.index') }}">
                    <span>{{ trans('Stock Count') }}</span>
                </a>
            </li>
        </ul>
    @endcan
    @can('inventorybalance')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('inventoryBalances*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('inventoryBalances*') ? 'active' : '' }}" href="{{ route('inventoryBalances.index') }}">
                    <span>{{ trans('side_menu.balances') }}</span>
                </a>
            </li>
        </ul>
    @endcan
    @can('inventorytransaction')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('inventoryTransactions*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('inventoryTransactions*') ? 'active' : '' }}" href="{{ route('inventoryTransactions.index') }}">
                    <span>{{ trans('side_menu.transactions') }}</span>
                </a>
            </li>
        </ul>
    @endcan
    <!-- @can('inventorytransfer')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('inventoryTransfers*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('inventoryTransfers*') ? 'active' : '' }}" href="{{ route('inventoryTransfers.index') }}">
                    <span>{{ trans('side_menu.transfers') }}</span>
                </a>
            </li>
        </ul>
    @endcan -->
</li>
@endcanany

@canany(['lorry','driver','kelindan','agent','supervisor','product','customer','specialprice','foc','assigns'])
<li class="nav-item nav-dropdown {{ Request::is('lorries*','servicedetails*','drivers*','driverLocations*','kelindans*','agents*','supervisors*','products*','customers*','specialprices*','focs*','assigns*') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-list"></i>
        <span>{{ trans('side_menu.master_data') }}</span>
    </a>

    <!-- @can('lorry')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('lorries*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('lorries*') ? 'active' : '' }}" href="{{ route('lorries.index') }}">
                    <span>{{ trans('side_menu.lorries') }}</span>
                </a>
            </li>
        </ul>
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('servicedetails*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('servicedetails*') ? 'active' : '' }}" href="{{ route('servicedetails.index') }}">
                    <span>{{ trans('side_menu.lorry_service') }}</span>
                </a>
            </li>
        </ul>
    @endcan -->

    @can('driver')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('drivers*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('drivers*') ? 'active' : '' }}" href="{{ route('drivers.index') }}">
                    <span>Agents</span>
                </a>
            </li>
        </ul>
        <!-- <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('driverLocations*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('driverLocations*') ? 'active' : '' }}" href="{{ route('driverLocations.index') }}">
                    <span>{{ trans('side_menu.driver_locations') }}</span>
                </a>
            </li>
        </ul> -->
    @endcan

    <!-- @can('kelindan')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('kelindans*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('kelindans*') ? 'active' : '' }}" href="{{ route('kelindans.index') }}">
                    <span>{{ trans('side_menu.kelindans') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('agent')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('agents*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('agents*') ? 'active' : '' }}" href="{{ route('agents.index') }}">
                    <span>{{ trans('side_menu.agents') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('supervisor')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('supervisors*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('supervisors*') ? 'active' : '' }}" href="{{ route('supervisors.index') }}">
                    <span>{{ trans('side_menu.operations') }}</span>
                </a>
            </li>
        </ul>
    @endcan -->

    @can('product')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('products*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('products*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                    <span>{{ trans('side_menu.products') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('productcategory')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('productCategories*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('productCategories*') ? 'active' : '' }}" href="{{ route('productCategories.index') }}">
                    <span>{{ trans('Product Categories') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    <!-- @can('company')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('companies*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('companies*') ? 'active' : '' }}" href="{{ route('companies.index') }}">
                    <span>{{ trans('side_menu.companies') }}</span>
                </a>
            </li>
        </ul>
    @endcan -->
    
    @can('customer')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('customers*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('customers*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                    <span>{{ trans('side_menu.customers') }}</span>
                </a>
            </li>
        </ul>
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('customer_group*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('customer_group*') ? 'active' : '' }}" href="{{ route('customer_group.index') }}">
                    <span>{{ trans('side_menu.customer_group') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('assign')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('assigns*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('assigns*') ? 'active' : '' }}" href="{{ route('assigns.index') }}">
                    <span>{{ trans('side_menu.assigns') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    <!-- @can('specialprice')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('specialprices*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('specialprices*') ? 'active' : '' }}" href="{{ route('specialPrices.index') }}">
                    <span>{{ trans('side_menu.special_prices') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('foc')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('focs*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('focs*') ? 'active' : '' }}" href="{{ route('focs.index') }}">
                    <span>{{ trans('side_menu.focs') }}</span>
                </a>
            </li>
        </ul>
    @endcan -->

</li>
@endcanany

<!-- @canany(['code'])
<li class="nav-item nav-dropdown {{ Request::is('codes*') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-settings"></i>
        <span>{{ trans('side_menu.setup') }}</span>
    </a>

    @can('code')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('codes*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('codes*') ? 'active' : '' }}" href="{{ route('codes.index') }}">
                    <span>{{ trans('side_menu.codes') }}</span>
                </a>
            </li>
            <li class="nav-item {{ Request::is('customer_group*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('customer_group*') ? 'active' : '' }}" href="{{ route('customer_group.index') }}">
                    <span>{{ trans('side_menu.customer_group') }}</span>
                </a>
            </li>
            <li class="nav-item {{ Request::is('commission_group*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('commission_group*') ? 'active' : '' }}" href="{{ route('commission_group.index') }}">
                    <span>{{ trans('side_menu.commission_group') }}</span>
                </a>
            </li>
            <li class="nav-item {{ Request::is('language*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('language*') ? 'active' : '' }}" href="{{ route('language.index') }}">
                    <span>{{ trans('side_menu.system_language_setting') }}</span>
                </a>
            </li>
            <li class="nav-item {{ Request::is('mobile_language*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('mobile_language*') ? 'active' : '' }}" href="{{ route('mobile_language.index') }}">
                    <span>{{ trans('side_menu.mobile_app_language_setting') }}</span>
                </a>
            </li>
        </ul>
    @endcan

</li>
@endcanany
 -->

@canany(['user','userrole','role','rolepermission'])
<li class="nav-item nav-dropdown {{ Request::is('users*','userHasRoles*','roles*','roleHasPermissions*','permissions*') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-people"></i>
        <span>{{ trans('side_menu.user_management') }}</span>
    </a>

    @can('user')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('users*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('users*') ? 'active' : '' }}" href="{!! route('users.index') !!}">
                    <span>{{ trans('side_menu.users') }}</span>
                </a>
            </li>
        </ul>
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('Managerusers*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('Managerusers*') ? 'active' : '' }}" href="{!! route('Managerusers.index') !!}">
                    <span>Inventory Manager</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('userrole')
        <!--<ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('userHasRoles*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('userHasRoles*') ? 'active' : '' }}" href="{{ route('userHasRoles.index') }}">
                    <span>{{ trans('side_menu.user_roles') }}</span>
                </a>
            </li>
        </ul>-->
    @endcan

    @can('role')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('roles*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('roles*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                    <span>{{ trans('side_menu.roles') }}</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('rolepermission')
        <!--<ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('roleHasPermissions*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('roleHasPermissions*') ? 'active' : '' }}" href="{{ route('roleHasPermissions.index') }}">
                    <span>{{ trans('side_menu.role_permissions') }}</span>
                </a>
            </li>
        </ul>-->
    @endcan

    @if(env('APP_ENV') == 'local')
        <!--<ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('permissions*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('permissions*') ? 'active' : '' }}" href="{{ route('permissions.index') }}">
                    <span>{{ trans('side_menu.permissions') }}</span>
                </a>
            </li>
        </ul>-->
    @endif

</li>
@endcanany

@can('report')
    <li class="nav-item {{ Request::is('reports*') ? 'active' : '' }}">
        <a class="nav-link {{ Request::is('reports*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
            <i class="nav-icon icon-cursor"></i>
            <span>{{ trans('side_menu.reports') }}</span>
        </a>
    </li>

    @if(env('APP_ENV') == 'local')
    @endif

@endcan
