<?php

namespace App\DataTables;

use App\Models\InventoryTransaction;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class InventoryTransactionDataTable extends DataTable
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

            ->editColumn('created_at', function($request) {
                return $request->created_at ? $request->created_at->format('d-m-Y H:i') : '';
            })
            ->addColumn('action', 'inventory_transactions.datatables_actions')
            ->editColumn('type', function ($model) {
                $colors = [
                    InventoryTransaction::TYPE_STOCK_IN => 'success', // Green
                    InventoryTransaction::TYPE_STOCK_OUT => 'danger', // Red
                    InventoryTransaction::TYPE_STOCK_RETURN => 'warning', // Orange
                ];
                
                $labels = [
                    InventoryTransaction::TYPE_STOCK_IN => 'Stock In',
                    InventoryTransaction::TYPE_STOCK_OUT => 'Stock Out',
                    InventoryTransaction::TYPE_STOCK_RETURN => 'Stock Return',
                ];
                
                $color = $colors[$model->type] ?? 'secondary';
                $label = $labels[$model->type] ?? 'Unknown';
                
                return '<span class="badge badge-' . $color . '">' . $label . '</span>';
            })
            
            ->editColumn('quantity', function ($model) {
                $colors = [
                    InventoryTransaction::TYPE_STOCK_IN => 'text-success font-weight-bold',
                    InventoryTransaction::TYPE_STOCK_OUT => 'text-danger font-weight-bold',
                    InventoryTransaction::TYPE_STOCK_RETURN => 'text-warning font-weight-bold',
                ];
                
                $sign = $model->type == InventoryTransaction::TYPE_STOCK_IN ? '+' : '-';
                $colorClass = $colors[$model->type] ?? '';
                
                return '<span class="' . $colorClass . '">' . $sign . abs($model->quantity) . '</span>';
            })
            ->rawColumns(['type', 'quantity', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\InventoryTransaction $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(InventoryTransaction $model)
    {
        return $model->newQuery()
        ->with('driver:id,name')
        ->with('product:id,name')
        ->select('inventory_transactions.*');
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
            // ->addAction(['width' => '120px', 'printable' => false])
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => false,
                'order'     => [[0, 'desc']],
                'lengthMenu' => [[ 10, 50, 100, 300 ],[ '10 rows', '50 rows', '100 rows', '300 rows' ]],
                'buttons' => [
                    // [
                    //     'extend' => 'create',
                    //     'className' => 'btn btn-default btn-sm no-corner',
                    //     'text' => '<i class="fa fa-plus"></i> ' . trans('table_buttons.create'),
                    // ],
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
                
                'initComplete' => 'function(){
                    var columns = this.api().init().columns;
                    this.api()
                    .columns()
                    .every(function (index) {
                        var column = this;
                        if(columns[index].searchable){
                            if(columns[index].title == \'Type\'){
                                var input = \'<select class="border-0" id="typeStock" style="width: 100%;"><option value=""></option><option value="1">Stock In</option><option value="2">Stock Out</option><option value="3">Stock Return</option></select>\';
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
            'date'=> new \Yajra\DataTables\Html\Column(['title' => trans('inventory_transactions.date'),
            'data' => 'created_at',
            'name' => 'created_at']),

            'type'=> new \Yajra\DataTables\Html\Column(['title' => trans('inventory_transactions.type'),
            'data' => 'type',
            'name' => 'inventory_transactions.type']),

            'driver_id'=> new \Yajra\DataTables\Html\Column(['title' => trans('Driver'),
            'data' => 'driver.name',
            'name' => 'driver.name']),

            'product_id'=> new \Yajra\DataTables\Html\Column(['title' => trans('inventory_transactions.product'),
            'data' => 'product.name',
            'name' => 'product.name']),

            'quantity'=> new \Yajra\DataTables\Html\Column(['title' => trans('inventory_transactions.quantity'),
            'data' => 'quantity',
            'name' => 'quantity']),

            'remark'=> new \Yajra\DataTables\Html\Column(['title' => trans('inventory_transactions.remark'),
            'data' => 'remark',
            'name' => 'remark']),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'inventory_transactions_datatable_' . time();
    }
}
