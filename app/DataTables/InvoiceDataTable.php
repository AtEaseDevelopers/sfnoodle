<?php

namespace App\DataTables;

use App\Models\Invoice;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use App\Models\Code;

class InvoiceDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);

        return $dataTable->addColumn('action', 'invoices.datatables_actions');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Invoice $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Invoice $model)
    {
        return $model->newQuery()
        ->with('customer')
        ->with('driver:id,name')
        ->with('kelindan:id,name')
        ->with('agent:id,name')
        ->with('supervisor:id,name')
        ->with('invoicedetail')
        ->select('invoices.*');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->addAction(['width' => '120px', 'printable' => false])
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => false,
                'order'     => [[2, 'desc']],
                'lengthMenu' => [[ 10, 50, 100, 300 ],[ '10 rows', '50 rows', '100 rows', '300 rows' ]],
                'buttons'   => [
                    ['extend' => 'create', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'print', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'reset', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'reload', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'excelHtml5','text'=>'<i class="fa fa-file-excel-o"></i> Excel','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'invoice'.date('dmYHis')],
                    ['extend' => 'pdfHtml5', 'orientation' => 'landscape', 'pageSize' => 'LEGAL','text'=>'<i class="fa fa-file-pdf-o"></i> PDF','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'invoice'.date('dmYHis')],
                    ['extend' => 'colvis', 'className' => 'btn btn-default btn-sm no-corner','text'=>'<i class="fa fa-columns"></i> Column',],
                    ['extend' => 'pageLength','className' => 'btn btn-default btn-sm no-corner',],
                ],
                'columnDefs' => [
                    [
                        'targets' => -1,
                        'visible' => true
                    ],
                    [
                        'targets' => 0,
                        'visible' => true,
                        'render' => 'function(data, type){return "<input type=\'checkbox\' class=\'checkboxselect\' checkboxid=\'"+data+"\'/>";}'
                    ],
                    [
                        'targets' => 8,
                        'visible' => true,
                        'render' => 'function(data, type){var totalprice = 0; $.each(data,function(index,value){ totalprice=totalprice+parseFloat(value.totalprice) }); return totalprice.toFixed(2);}'
                    ],
                    [
                    'targets' => 9,
                    'render' => 'function(data, type, row){
                            var paymentTerms = {
                                1: \'Cash\',
                                2: \'Credit\',
                                3: \'Online BankIn\',
                                4: \'E-wallet\',
                                5: \'Cheque\'
                            };
                            return paymentTerms[data] || \'Unknown\';
                        }'
                    ],
                    [
                    'targets' => 10,
                    'render' => 'function(data, type){return data == 1 ? "Completed" : "New";}'
                    ],
                    [
                    'targets' => 11,
                    'render' => 'function(data, type){
                        if(data == 1) {
                            return "Synced";
                        } else if (data == 2) {
                            return "Voided";
                        }
                        
                    }'
                    ],
                ],
                'initComplete' => 'function(){
                    var columns = this.api().init().columns;
                    this.api()
                    .columns()
                    .every(function (index) {
                        var column = this;
                        if(columns[index].searchable){
                            if(columns[index].title == \'Status\'){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="1">Completed</option><option value="0">New</option></select>\';
                            }else if(columns[index].title == \'Payment Term\'){
                                var input = \'<select class="border-0" style="width: 100%;"><option value=""></option><option value="1">Cash</option><option value="2">Credit</option><option value="3">Online BankIn</option><option value="4">E-wallet</option><option value="5">Cheque</option></select>\';
                            }else if(columns[index].title == \'Date\'){
                                var input = \'<input type="text" id="\'+index+\'Date" onclick="searchDateColumn(this);" placeholder="Search ">\';
                            }else if(columns[index].title == \'Group\'){
                                var input = \'<select id="group" class="border-0" style="width: 100%;"><option value=""></option></select>\';
                            }else{
                                var input = \'<input type="text" placeholder="Search ">\';
                            }
                            $(input).appendTo($(column.footer()).empty()).on(\'change\', function(){
                                column.search($(this).val(),true,false).draw();
                                ShowLoad();
                            })
                        }
                    });
                    var groupItems = '.json_encode(Code::where('code','customer_group')->pluck('description','value')->toArray()).';
                    var x = document.getElementById("group");
                    $.each(groupItems, function( index, value ) {
                        var option = document.createElement("option");
                        option.text = value;
                        option.value = index;
                        x.add(option);
                    });
                }'
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'checkbox'=> new \Yajra\DataTables\Html\Column(['title' => '<input type="checkbox" id="selectallcheckbox">',
            'data' => 'id',
            'name' => 'id',
            'orderable' => false,
            'searchable' => false]),

            'invoiceno'=> new \Yajra\DataTables\Html\Column(['title' => 'Invoice No',
            'data' => 'invoiceno',
            'name' => 'invoiceno']),

            'date',

            'customer_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Customer',
            'data' => 'customer.company',
            'name' => 'customer.company']),

            'driver_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Driver',
            'data' => 'driver.name',
            'name' => 'driver.name']),

            'kelindan_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Kelindan',
            'data' => 'kelindan.name',
            'name' => 'kelindan.name']),

            'agent_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Agent',
            'data' => 'agent.name',
            'name' => 'agent.name']),

            'supervisor_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Supervisor',
            'data' => 'supervisor.name',
            'name' => 'supervisor.name']),

            'total'=> new \Yajra\DataTables\Html\Column(['title' => 'Total Price',
            'data' => 'invoicedetail',
            'name' => 'invoicedetail',
            'searchable' => false]),

            'paymentterm'=> new \Yajra\DataTables\Html\Column(['title' => 'Payment Term',
            'data' => 'paymentterm',
            'name' => 'invoices.paymentterm']),

            'status'=> new \Yajra\DataTables\Html\Column(['title' => 'Status',
            'data' => 'status',
            'name' => 'invoices.status']),
            
            // 'xero_status'=> new \Yajra\DataTables\Html\Column(['title' => 'Xero Status',
            // 'data' => 'xero_status',
            // 'name' => 'invoices.xero_status']),

            // 'remark',

            'group'=> new \Yajra\DataTables\Html\Column(['title' => 'Group',
            'data' => 'customer.GroupDescription',
            'name' => 'customer.group',
            'orderable' => false]),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'invoices_datatable_' . time();
    }
}
