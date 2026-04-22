<?php

namespace App\DataTables;

use App\Models\InvoicePayment;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use App\Models\Code;

class InvoicePaymentDataTable extends DataTable
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
            ->addColumn('payment_no', function ($row) {
                return 'PR' . str_pad($row->id, 5, '0', STR_PAD_LEFT);
            })
            ->addColumn('invoice_no', function ($row) {
                return $row->invoice->invoiceno ?? '-';
            })
            ->addColumn('type', function ($row) {
                if ($row->type == 1) {
                    return 'Cash';
                } elseif ($row->type == 2) {
                    return 'Credit';
                }
                return $row->type;
            })
            ->addColumn('amount_formatted', function ($row) {
                return number_format($row->amount, 2, '.', ',');
            })
            ->addColumn('approve_at_formatted', function ($row) {
                return $row->approve_at ? \Carbon\Carbon::parse($row->approve_at)->format('Y-m-d H:i:s') : '-';
            })
            ->addColumn('created_at_formatted', function ($row) {
                return $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : '-';
            })
            ->addColumn('action', 'invoice_payments.datatables_actions')
            
            // Add filter for invoice_no
            ->filterColumn('invoice_no', function($query, $keyword) {
                $query->whereHas('invoice', function($q) use ($keyword) {
                    $q->where('invoiceno', 'like', "%{$keyword}%");
                });
            })
            
            // Add filter for payment_no
            ->filterColumn('payment_no', function($query, $keyword) {
                $query->whereRaw("CONCAT('PR', LPAD(invoice_payments.id, 5, '0')) LIKE ?", ["%{$keyword}%"]);
            })
            
            // Add filter for approve_at
            ->filterColumn('approve_at_formatted', function($query, $keyword) {
                if (!empty($keyword)) {
                    $query->whereDate('approve_at', '=', $keyword);
                }
            })
            
            // Add filter for customer company
            ->filterColumn('customer.company', function($query, $keyword) {
                $query->whereHas('customer', function($q) use ($keyword) {
                    $q->where('company', 'like', "%{$keyword}%");
                });
            })
            
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\InvoicePayment $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(InvoicePayment $model)
    {
        return $model->newQuery()
            ->with(['invoice:id,invoiceno', 'customer'])
            ->select('invoice_payments.*')
            ->orderBy('created_at', 'desc');
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
            ->addAction(['title' => trans('invoice_payments.action'), 'printable' => false])
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => false,
                'order'     => [[1, 'desc']],
                'lengthMenu' => [[10, 50, 100, 300], ['10 rows', '50 rows', '100 rows', '300 rows']],
                'buttons' => [
                    [
                        'extend' => 'create',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-plus"></i> ' . trans('table_buttons.create'),
                    ],
                    [
                        'extend' => 'print',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-print"></i> ' . trans('table_buttons.print'),
                    ],
                    [
                        'extend' => 'reset',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-refresh"></i> ' . trans('table_buttons.reset'),
                    ],
                    [
                        'extend' => 'reload',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-refresh"></i> ' . trans('table_buttons.reload'),
                    ],
                    [
                        'extend' => 'excelHtml5',
                        'text' => '<i class="fa fa-file-excel-o"></i> ' . trans('table_buttons.excel'),
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'invoice' . date('dmYHis')
                    ],
                    [
                        'extend' => 'pdfHtml5',
                        'orientation' => 'landscape',
                        'pageSize' => 'LEGAL',
                        'text' => '<i class="fa fa-file-pdf-o"></i> ' . trans('table_buttons.pdf'),
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'invoice' . date('dmYHis')
                    ],
                    [
                        'extend' => 'colvis',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-columns"></i> ' . trans('table_buttons.column')
                    ],
                    [
                        'extend' => 'pageLength',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => trans('table_buttons.show_10_rows')
                    ],
                ],
                'columnDefs' => [
                    [
                        'targets' => 0,
                        'orderable' => false,
                        'searchable' => false,
                        'className' => 'dt-body-center',
                        'render' => 'function(data, type, row){
                            return \'<input type="checkbox" class="checkboxselect" checkboxid="\'+data+\'"/>\';
                        }'
                    ],
                    [
                        'targets' => 5, // Amount column
                        'className' => 'dt-body-right'
                    ],
                    [
                        'targets' => 6, // Type column
                        'render' => 'function(data, type){
                            if (data == 1) {
                                return "Cash";
                            } else if (data == 2) {
                                return "Credit";
                            } else {
                                return data;
                            }
                        }'
                    ],
                    [
                        'targets' => 7, // Status column
                        'render' => 'function(data, type){
                            if (data == 0) {
                                return "Cancelled";
                            } else if (data == 1) {
                                return "Completed";
                            } else {
                                return "Unknown";
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
                            if(columns[index].title == "Status"){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="">All</option><option value="1">Completed</option><option value="0">Cancelled</option></select>\';
                            }else if(columns[index].title == "Type"){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="">All</option><option value="1">Cash</option><option value="2">Credit</option></select>\';
                            }else if(columns[index].title == "Approve At" || columns[index].title == "Date"){
                                var input = \'<input type="text" id="\'+index+\'Date" onclick="searchDateColumn(this);" placeholder="Search">\';
                            }else{
                                var input = \'<input type="text" placeholder="Search">\';
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
            [
                'title' => '<input type="checkbox" id="selectallcheckbox">',
                'data' => 'id',
                'name' => 'id',
                'orderable' => false,
                'searchable' => false
            ],
            [
                'title' => trans('invoice_payments.date'),
                'data' => 'created_at_formatted',
                'name' => 'created_at_formatted',
                'searchable' => true
            ],
            [
                'title' => trans('invoice_payments.customer'),
                'data' => 'customer.company',
                'name' => 'customer.company',
                'searchable' => true
            ],
            [
                'title' => trans('invoice_payments.invoice_no'),
                'data' => 'invoice_no',
                'name' => 'invoice_no',
                'orderable' => true,  // Changed to true
                'searchable' => true   // Added searchable
            ],
            [
                'title' => trans('invoice_payments.payment_no'),
                'data' => 'payment_no',
                'name' => 'payment_no',
                'searchable' => true
            ],
            [
                'title' => trans('invoice_payments.amount'),
                'data' => 'amount_formatted',
                'name' => 'amount_formatted',
                'orderable' => false,
                'searchable' => false
            ],
            [
                'title' => trans('invoice_payments.type'),
                'data' => 'type',
                'name' => 'type',
                'searchable' => true
            ],
            [
                'title' => trans('invoice_payments.status'),
                'data' => 'status',
                'name' => 'status',
                'searchable' => true
            ],
            [
                'title' => trans('invoice_payments.approve_by'),
                'data' => 'approve_by',
                'name' => 'approve_by',
                'searchable' => true
            ],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'invoice_payments_datatable_' . time();
    }
}