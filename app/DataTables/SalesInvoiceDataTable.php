<?php

namespace App\DataTables;

use App\Models\SalesInvoice;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use App\Models\Code;

class SalesInvoiceDataTable extends DataTable
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

        return $dataTable
            ->addColumn('action', 'sales_invoices.datatables_actions')
            ->editColumn('date', function ($model) {
                return $model->date ? date('d-m-Y', strtotime($model->date)) : '';
            })
            ->editColumn('created_by', function ($model) {
                return $model->creator ? $model->creator->name : '';
            })
            ->filterColumn('date', function ($query, $keyword) {
                $query->whereDate('date', date('Y-m-d', strtotime($keyword)));
            });
    }
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\SalesInvoice $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(SalesInvoice $model)
    {
        return $model->newQuery()
        ->with(['customer', 'salesInvoiceDetails','driver'])
        ->selectRaw('sales_invoices.*, 
            (SELECT SUM(totalprice) FROM sales_invoice_details 
             WHERE sales_invoice_details.sales_invoice_id = sales_invoices.id) as calculated_total')
        ->select('sales_invoices.*');
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
            ->addAction(['title' => 'Action', 'printable' => false])
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => false,
                'order'     => [[2, 'desc']],
                'lengthMenu' => [[ 10, 50, 100, 300 ],[ '10 rows', '50 rows', '100 rows', '300 rows' ]],
                'buttons' => [
                    [
                        'extend' => 'create',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-plus"></i> Create',
                    ],
                    [
                        'extend' => 'print',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-print"></i> Print',
                    ],
                    [
                        'extend' => 'reset',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-refresh"></i> Reset',
                    ],
                    [
                        'extend' => 'reload',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-refresh"></i> Reload',
                    ],
                    [
                        'extend' => 'excelHtml5',
                        'text' => '<i class="fa fa-file-excel-o"></i> Excel',
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'sales_invoice' . date('dmYHis')
                    ],
                    [
                        'extend' => 'pdfHtml5',
                        'orientation' => 'landscape',
                        'pageSize' => 'LEGAL',
                        'text' => '<i class="fa fa-file-pdf-o"></i> PDF',
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'sales_invoice' . date('dmYHis')
                    ],
                    [
                        'extend' => 'colvis',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-columns"></i> Column'
                    ],
                    [
                        'extend' => 'pageLength',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => 'Show 10 rows'
                    ],
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
                        'targets' => 5, // This is the total price column
                        'visible' => true,
                        'render' => 'function(data, type, row){
                            if(type === "display" || type === "filter"){
                                var totalprice = 0;
                                if(data && Array.isArray(data)){
                                    $.each(data, function(index, value){
                                        if(value && value.totalprice){
                                            totalprice += parseFloat(value.totalprice) || 0;
                                        }
                                    });
                                }
                                // Alternative: if you have a total field directly in the invoice
                                if(row.total && !isNaN(parseFloat(row.total))){
                                    totalprice = parseFloat(row.total);
                                }
                                return totalprice.toFixed(2);
                            }
                            return data;
                        }'
                    ],
                    [
                    'targets' => 6,
                    'render' => 'function(data, type, row){
                            var paymentTerms = {
                                Cash: \'Cash\',
                                Credit: \'Credit\',
                            };
                            return paymentTerms[data] || \'Unknown\';
                        }'
                    ],
                    [
                    'targets' => 7,
                            'render' => 'function(data, type, row){
                                var statuses = {
                                    0: "Pending",    
                                    1: "Cancelled",  
                                    2: "Convert To Invoice"
                                };
                                return statuses[data] || "Unknown";
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
                            var input = \'<select class="border-0" style="width: 100%;">\' +
                                        \'<option value="">All</option>\' +
                                        \'<option value="0">Pending</option>\' +
                                        \'<option value="1">Cancelled</option>\' +
                                        \'<option value="2">Convert To Invoice</option>\' +

                                        \'</select>\';
                           }else if(columns[index].title == \'Payment Term\'){
                                var input = \'<select class="border-0" style="width: 100%;">\' +
                                            \'<option value="">All</option>\' +
                                            \'<option value="Cash">Cash</option>\' +
                                            \'<option value="Credit">Credit</option>\' +
                                            \'</select>\';
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
                    if(x){
                        $.each(groupItems, function( index, value ) {
                            var option = document.createElement("option");
                            option.text = value;
                            option.value = index;
                            x.add(option);
                        });
                    }
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
            'searchable' => false
            ]),

            'invoiceno' => new \Yajra\DataTables\Html\Column([
                'title' => 'Invoice No',
                'data' => 'invoiceno',
                'name' => 'invoiceno'
            ]),

            'date' => new \Yajra\DataTables\Html\Column([
                'title' => 'Date',
                'data' => 'date',
                'name' => 'date'
            ]),

            'customer_id' => new \Yajra\DataTables\Html\Column([
                'title' => 'Customer',
                'data' => 'customer.company',
                'name' => 'customer.company'
            ]),

            'driver' => new \Yajra\DataTables\Html\Column([
                'title' => 'Agent',
                'data' => 'driver.name',
                'name' => 'driver.name'
            ]),

            'total' => new \Yajra\DataTables\Html\Column([
                'title' => 'Total Amount',
                'data' => 'sales_invoice_details', 
                'name' => 'sales_invoice_details', 
                'searchable' => false
            ]),

            'paymentterm' => new \Yajra\DataTables\Html\Column([
                'title' => 'Payment Term',
                'data' => 'paymentterm',
                'name' => 'sales_invoices.paymentterm'
            ]),

            'status' => new \Yajra\DataTables\Html\Column([
                'title' => 'Status',
                'data' => 'status',
                'name' => 'sales_invoices.status'
            ]),

            'created_by' => new \Yajra\DataTables\Html\Column([
                'title' => 'Created By',
                'data' => 'created_by',
                'name' => 'sales_invoices.created_by'
            ]),
     
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'sales_invoices_datatable_' . time();
    }
}