<?php

namespace App\DataTables;

use App\Models\Driver;
use App\Models\Product;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\DataTables;

class InventoryBalanceDataTable extends DataTable
{
    protected $productOrder = [];
    
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return DataTables::of($query)
            ->addColumn('product_details', function($driver) {
                // Get all products with their quantities
                $productQuantities = [];
                
                foreach ($driver->inventoryBalances as $balance) {
                    if ($balance->quantity != 0 && $balance->product) {
                        $productQuantities[$balance->product->name] = $balance->quantity;
                    }
                }
                
                // Ensure all products are in the same order
                $orderedItems = [];
                foreach ($this->getProductOrder() as $productName) {
                    if (isset($productQuantities[$productName])) {
                        $orderedItems[] = '<strong>' . e($productName) . '</strong>: ' . $productQuantities[$productName];
                    }
                }
                
                // Add any remaining products (in case new products were added)
                foreach ($productQuantities as $product => $quantity) {
                    if (!in_array($product, $this->getProductOrder())) {
                        $orderedItems[] = '<strong>' . e($product) . '</strong>: ' . $quantity;
                    }
                }
                
                if (empty($orderedItems)) {
                    return '<span class="text-muted">No items</span>';
                }
                
                return implode(' | ', $orderedItems);
            })
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
            ->rawColumns(['product_details', 'total_quantity', 'product_count'])
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
                      ->with('product:id,name');
            }])
            ->whereHas('inventoryBalances', function($query) {
                $query->where('quantity', '<>', 0);
            })
            ->select('drivers.*')
            ->orderBy('name');
    }

    /**
     * Get the product order from database
     */
    protected function getProductOrder()
    {
        if (empty($this->productOrder)) {
            // Get all product names sorted alphabetically
            $this->productOrder = Product::orderBy('name')->pluck('name')->toArray();
        }
        
        return $this->productOrder;
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
                'width' => '15%'
            ],
            [
                'data' => 'product_details',
                'title' => 'Products & Quantities',
                'orderable' => false,
                'searchable' => false,
                'width' => '60%'
            ],
            [
                'data' => 'product_count',
                'title' => 'Types',
                'orderable' => false,
                'searchable' => false,
                'width' => '10%',
                'className' => 'text-center'
            ],
            [
                'data' => 'total_quantity',
                'title' => 'Total Items',
                'orderable' => true,
                'searchable' => false,
                'width' => '10%',
                'className' => 'text-center font-weight-bold'
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