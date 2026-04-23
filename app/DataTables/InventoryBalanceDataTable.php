<?php

namespace App\DataTables;

use App\Models\Driver;
use App\Models\Product;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\DataTables;

class InventoryBalanceDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return DataTables::of($query)
            ->addColumn('total_quantity', function($driver) {
                $total = $driver->inventoryBalances->sum('quantity');
                return '<span class="font-weight-bold text-primary">' . $total . '</span>';
            })
            ->addColumn('product_count', function($driver) {
                $count = $driver->inventoryBalances
                    ->where('quantity', '<>', 0)
                    ->unique('product_id')
                    ->count();
                return '<span class="badge badge-secondary">' . $count . '</span>';
            })
            ->addColumn('action', function($row) {
                return '<button type="button" class="btn btn-info btn-sm view-details-btn" data-id="' . $row->id . '" data-name="' . e($row->driver->name ?? 'N/A') . '">
                            <i class="fa fa-eye"></i> View Products
                        </button>';
            })
            ->rawColumns(['total_quantity', 'product_count', 'action'])
            ->addIndexColumn();
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Driver $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Driver $model)
    {
        return $model->newQuery()
            ->with(['inventoryBalances' => function($query) {
                $query->where('quantity', '<>', 0)
                      ->with('product:id,name,code');
            }])
            ->whereHas('inventoryBalances', function($query) {
                $query->where('quantity', '<>', 0);
            })
            ->select('drivers.*')
            ->orderBy('name');
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
            ->parameters([
                'dom'       => '<"row"<"col-md-6"B><"col-md-6"f>><"row"<"col-md-12"tr>><"row"<"col-md-5"i><"col-md-7"p>>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => true,
                'order'     => [[1, 'asc']],
                'lengthMenu' => [[10, 25, 50, 100, 300], [10, 25, 50, 100, 300]],
                'pageLength' => 25,
                'buttons' => [
                    [
                        'extend' => 'print',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-print"></i> Print',
                        'exportOptions' => ['columns' => [0, 1, 2, 3]]
                    ],
                    [
                        'extend' => 'excel',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-file-excel-o"></i> Excel',
                        'exportOptions' => ['columns' => [0, 1, 2, 3]]
                    ],
                    [
                        'extend' => 'pdf',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-file-pdf-o"></i> PDF',
                        'exportOptions' => ['columns' => [0, 1, 2, 3]]
                    ],
                    [
                        'extend' => 'colvis',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-columns"></i> Columns'
                    ],
                    [
                        'extend' => 'reload',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-refresh"></i> Reload'
                    ]
                ],
                'language' => [
                    'emptyTable' => 'No inventory data available',
                    'zeroRecords' => 'No matching records found'
                ]
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
                'data' => 'DT_RowIndex',
                'title' => '#',
                'orderable' => false,
                'searchable' => false,
                'width' => '5%',
                'className' => 'text-center'
            ],
            [
                'data' => 'name',
                'title' => 'Agent',
                'width' => '25%'
            ],
            [
                'data' => 'product_count',
                'title' => 'Product Types',
            'orderable' => false,
                'searchable' => false,
                'width' => '15%',
                'className' => 'text-center'
            ],
            [
                'data' => 'total_quantity',
                'title' => 'Total Items',
                'orderable' => true,
                'searchable' => false,
                'width' => '15%',
                'className' => 'text-center font-weight-bold'
            ],
            [
                'data' => 'action',
                'title' => 'Action',
                'orderable' => false,
                'searchable' => false,
                'width' => '20%',
                'className' => 'text-center'
            ]
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'inventory_summary_' . date('Y_m_d');
    }
}