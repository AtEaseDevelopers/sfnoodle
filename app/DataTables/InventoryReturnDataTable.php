<?php

namespace App\DataTables;

use App\Models\InventoryReturn;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Illuminate\Support\Facades\Auth;

class InventoryReturnDataTable extends DataTable
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

        $dataTable->addColumn('action', function($request) {
            return view('inventory_returns.datatables_actions', compact('request'))->render();
        });
        // Format status with badge
        $dataTable->editColumn('status', function($request) {
            $badgeClass = $this->getStatusBadgeClass($request->status);
            return '<span class="badge ' . $badgeClass . '">' . ucfirst($request->status) . '</span>';
        });

        // Format dates
        $dataTable->editColumn('created_at', function($request) {
            return $request->created_at ? $request->created_at->format('d-m-Y H:i') : '';
        });

        $dataTable->editColumn('approved_at', function($request) {
            return $request->approved_at ? $request->approved_at->format('d-m-Y H:i') : '';
        });

        $dataTable->editColumn('rejected_at', function($request) {
            return $request->rejected_at ? $request->rejected_at->format('d-m-Y H:i') : '';
        });

        return $dataTable->rawColumns(['action', 'status', 'rejection_reason']);
    }

    /**
     * Get status badge class
     */
    private function getStatusBadgeClass($status)
    {
        switch ($status) {
            case 'pending':
                return 'bg-warning';
            case 'approved':
                return 'bg-success';
            case 'rejected':
                return 'bg-danger';
            default:
                return 'bg-info';
        }
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\InventoryReturn $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(InventoryReturn $model)
    {
        $query = $model->newQuery()
            ->with([
                'driver:id,name',
                'product:id,name',
                'approver:id,name',
                'rejector:id,name'
            ])
            ->select('inventory_returns.*');


        // Filter by status if requested
        if ($this->request()->has('status') && $this->request()->status != 'all') {
            $query->where('status', $this->request()->status);
        }

        // Filter by driver_id if provided
        if ($this->request()->has('driver_id')) {
            $query->where('driver_id', $this->request()->driver_id);
        }

        // Filter by product_id if provided
        if ($this->request()->has('product_id')) {
            $query->where('product_id', $this->request()->product_id);
        }

        // Date range filters
        if ($this->request()->has('date_from')) {
            $query->whereDate('created_at', '>=', $this->request()->date_from);
        }

        if ($this->request()->has('date_to')) {
            $query->whereDate('created_at', '<=', $this->request()->date_to);
        }

        return $query;
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
            ->addAction([
                'width' => '120px', 
                'printable' => false,
                'exportable' => false,
                'searchable' => false,
                'title' => 'Actions'
            ])
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => false,
                'order'     => [[0, 'desc']],
                'lengthMenu' => [[10, 50, 100, 300], ['10 rows', '50 rows', '100 rows', '300 rows']],
                'buttons' => [
                    [
                        'extend' => 'create',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-plus"></i> Create',
                        'action' => 'function(e, dt, node, config) {
                            $("#createRequest").modal("show");
                        }'
                    ],
                    ['extend' => 'print', 'className' => 'btn btn-default btn-sm no-corner', 'text' => '<i class="fa fa-print"></i> Print'],
                    ['extend' => 'reset', 'className' => 'btn btn-default btn-sm no-corner', 'text' => '<i class="fa fa-undo"></i> Reset'],
                    ['extend' => 'reload', 'className' => 'btn btn-default btn-sm no-corner', 'text' => '<i class="fa fa-refresh"></i> Reload'],
                    [
                        'extend' => 'excelHtml5',
                        'text' => '<i class="fa fa-file-excel-o"></i> Excel',
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => 'Inventory_Requests_' . date('dmYHis'),
                        'filename' => 'Inventory_Requests_' . date('dmYHis')
                    ],
                    [
                        'extend' => 'pdfHtml5',
                        'orientation' => 'landscape',
                        'pageSize' => 'LEGAL',
                        'text' => '<i class="fa fa-file-pdf-o"></i> PDF',
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => 'Inventory_Requests_' . date('dmYHis'),
                        'filename' => 'Inventory_Requests_' . date('dmYHis')
                    ],
                    [
                        'extend' => 'colvis',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-columns"></i> Columns'
                    ],
                    [
                        'extend' => 'pageLength',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => 'Show 10 rows'
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
                                var input = \'<select class="border-0" style="width: 100%;"><option value="">All</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option></select>\';
                            }else if(columns[index].title == "Created At"){
                                var input = \'<input type="text" id="\'+index+\'Date" onclick="searchDateColumn(this);" placeholder="Search">\';
                            }else{
                                var input = \'<input type="text" placeholder="Search">\';
                            }
                            $(input).appendTo($(column.footer()).empty()).on(\'change\', function(){
                                column.search($(this).val(),true,false).draw();
                                ShowLoad();
                            });
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
                'title' => 'Driver',
                'data' => 'driver.name',
                'name' => 'driver.name',
                'searchable' => true,
                'orderable' => true
            ],
            [
                'title' => 'Product',
                'data' => 'product.name',
                'name' => 'product.name',
                'searchable' => true,
                'orderable' => true
            ],
            [
                'title' => 'Quantity',
                'data' => 'quantity',
                'name' => 'quantity',
                'searchable' => true,
                'orderable' => true
            ],
            [
                'title' => 'Status',
                'data' => 'status',
                'name' => 'status',
                'searchable' => true,
                'orderable' => true
            ],
            [
                'title' => 'Requested At',
                'data' => 'created_at',
                'name' => 'created_at',
                'searchable' => true,
                'orderable' => true
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
        return 'inventory_returns_datatable_' . time();
    }
}