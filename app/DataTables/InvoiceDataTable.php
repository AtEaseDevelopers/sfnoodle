<?php

namespace App\DataTables;

use App\Models\Invoice;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

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

        return $dataTable
            ->addColumn('action', 'invoices.datatables_actions')
            ->editColumn('date', function ($model) {
                return $model->date ? date('d-m-Y', strtotime($model->date)) : '';
            })
            ->editColumn('created_by', function ($model) {
                // Use the creator_name accessor
                return $model->creator_name ?? 'Unknown';
            })
            ->filterColumn('date', function ($query, $keyword) {
                $query->whereDate('date', date('Y-m-d', strtotime($keyword)));
            })
            ->filterColumn('created_by', function ($query, $keyword) {
                // Add filtering for creator name
                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('createdByUser', function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', '%' . $keyword . '%');
                    })->orWhereHas('createdByDriver', function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', '%' . $keyword . '%');
                    });
                });
            }); 
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
            ->with([
                'customer', 
                'invoiceDetails',
                'driver',
                'createdByUser:id,name',
                'createdByDriver:id,name'
            ])
            ->selectRaw('invoices.*, 
                (SELECT SUM(totalprice) FROM invoice_details 
                 WHERE invoice_details.invoice_id = invoices.id) as calculated_total')
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
                        'filename' => 'invoice' . date('dmYHis')
                    ],
                    [
                        'extend' => 'pdfHtml5',
                        'orientation' => 'landscape',
                        'pageSize' => 'LEGAL',
                        'text' => '<i class="fa fa-file-pdf-o"></i> PDF',
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'invoice' . date('dmYHis')
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
                    'targets' => 8,
                    'render' => 'function(data, type){return data == 0 ? "Completed" : "Cancelled";}'
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
                                var input = \'<select class="border-0" style="width: 100%;"><option value="">All</option><option value="0">Completed</option><option value="1">Cancelled</option></select>\';
                                $(input).appendTo($(column.footer()).empty()).on(\'change\', function(){
                                    column.search($(this).val(), true, false).draw();
                                    ShowLoad();
                                });
                            }else if(columns[index].title == \'Payment Term\'){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="">All</option><option value="Cash">Cash</option><option value="Credit">Credit</option></select>\';
                            }else if(columns[index].title == \'Date\'){
                                var input = \'<input type="text" id="\'+index+\'Date" onclick="searchDateColumn(this);" placeholder="Search ">\';
                            }else{
                                var input = \'<input type="text" placeholder="Search ">\';
                            }
                            $(input).appendTo($(column.footer()).empty()).on(\'change\', function(){
                                column.search($(this).val(),true,false).draw();
                                ShowLoad();
                            })
                        }
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
                'title' => 'Total Price',
                'data' => 'invoice_details',
                'name' => 'invoice_details',
                'searchable' => false
            ]),

            'paymentterm' => new \Yajra\DataTables\Html\Column([
                'title' => 'Payment Term',
                'data' => 'paymentterm',
                'name' => 'invoices.paymentterm'
            ]),

            'created_by' => new \Yajra\DataTables\Html\Column([
                'title' => 'Created By',
                'data' => 'created_by', 
                'name' => 'created_by',
                'searchable' => true,
                'orderable' => true
            ]),

            'status' => new \Yajra\DataTables\Html\Column([
                'title' => 'Status',
                'data' => 'status',
                'name' => 'invoices.status'
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
        return 'invoices_datatable_' . time();
    }
}