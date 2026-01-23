<?php

namespace App\DataTables;

use App\Models\InventoryReturn;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;

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
        
        // Add item count column
        $dataTable->addColumn('item_count', function($request) {
            return $request->item_count;
        })->setRowClass(function($request) {
            return 'inventory-return-row';
        });

        // Add product summary column with hover tooltip like InventoryRequestDataTable
        $dataTable->addColumn('product_summary', function($request) {
            $items = $request->items ?? [];
            $summary = '';
            
            // Group items by product name
            $groupedItems = [];
            $tooltipItems = []; // For detailed breakdown in tooltip
            
            foreach ($items as $item) {
                $product = Product::find($item['product_id'] ?? null);
                
                $productName = $product ? $product->name : '-';
                $quantity = $item['quantity'] ?? 0;
                
                if (isset($groupedItems[$productName])) {
                    $groupedItems[$productName] += $quantity;
                } else {
                    $groupedItems[$productName] = $quantity;
                }
            }
            
            // Format the main display text (comma separated)
            if (!empty($groupedItems)) {
                $summaryParts = [];
                foreach ($groupedItems as $productName => $totalQuantity) {
                    $summaryParts[] = $productName . ' (x' . $totalQuantity . ')';
                    $tooltipItems[] = [
                        'name' => $productName,
                        'quantity' => $totalQuantity
                    ];
                }
                $summary = implode(', ', $summaryParts);
                
                // Build HTML for hover tooltip
                $tooltipHtml = '<div class="product-tooltip" style="text-align: left; min-width: 180px;">';
                $tooltipHtml .= '<div style="font-weight: bold; margin-bottom: 8px; border-bottom: 1px solid #ddd; padding-bottom: 4px;">Return Items:</div>';
                
                foreach ($tooltipItems as $index => $tooltipItem) {
                    $tooltipHtml .= '<div style="margin-bottom: 6px;">';
                    $tooltipHtml .= '<div style="display: flex; justify-content: space-between; align-items: center;">';
                    $tooltipHtml .= '<span style="font-weight: 500;">' . ($index + 1) . '. ' . e($tooltipItem['name']) . '</span>';
                    $tooltipHtml .= '<span style="margin-left: 10px; background-color: #4a90e2; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px;">' . $tooltipItem['quantity'] . '</span>';
                    $tooltipHtml .= '</div>';
                    $tooltipHtml .= '</div>';
                }
                
                $tooltipHtml .= '</div>';
                
            } else {
                $summary = '-';
                $tooltipHtml = '<div class="product-tooltip">No items</div>';
            }
            
            // Create unique ID for this tooltip
            $tooltipId = 'tooltip-' . uniqid();
            
            // Create the main display - tooltip on the entire container
            return '<div class="product-summary-container" 
                        style="position: relative; display: block; width: 100%; height: 100%; cursor: help;"
                        data-toggle="tooltip" 
                        data-html="true" 
                        data-placement="top" 
                        data-boundary="viewport"
                        title="' . htmlspecialchars($tooltipHtml) . '">
                        <div class="product-summary-text" 
                            style="display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            ' . e($summary) . '
                        </div>
                        <br>
                        <small class="text-muted">(' . count($items) . ' items)</small>
                    </div>';
        });

        // Add total quantity column
        $dataTable->addColumn('total_quantity', function($request) {
            return '<span class="badge badge-info">' . $request->total_quantity . '</span>';
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

        // Add remarks column
        $dataTable->editColumn('remarks', function($request) {
            return $request->remarks ? 
                '<span title="' . htmlspecialchars($request->remarks) . '" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block;">
                    ' . htmlspecialchars($request->remarks) . '
                </span>' : 
                '';
        });

        // Add driver name column
        $dataTable->addColumn('driver_name', function($request) {
            return $request->driver ? $request->driver->name : 'N/A';
        });

        // Add approver name column
        $dataTable->addColumn('approver_name', function($request) {
            if ($request->approver) {
                return $request->approver->name;
            }
            
            if ($request->approved_by) {
                // Fallback - show ID if approver relationship fails
                return 'User #' . $request->approved_by;
            }
            
            return 'N/A';
        });

        return $dataTable->rawColumns([
            'action', 
            'status', 
            'rejection_reason', 
            'remarks', 
            'product_summary',
            'total_quantity'
        ]);
    }

    /**
     * Get status badge class
     */
    private function getStatusBadgeClass($status)
    {
        switch ($status) {
            case 'pending':
                return 'badge-warning';
            case 'approved':
                return 'badge-success';
            case 'rejected':
                return 'badge-danger';
            default:
                return 'badge-info';
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
                'approver:id,name',
                'rejector:id,name'
            ])
            ->select('inventory_returns.*');

        // Get filter parameters from the DataTable request
        $request = request();
        
        // Check for regular request parameters (from custom filters)
        $status = $request->get('status', $this->request()->get('status'));
        $driverId = $request->get('driver_id', $this->request()->get('driver_id'));
        $productId = $request->get('product_id', $this->request()->get('product_id'));
        $dateFrom = $request->get('date_from', $this->request()->get('date_from'));
        $dateTo = $request->get('date_to', $this->request()->get('date_to'));

        // Filter by status if requested
        if (!empty($status) && $status != 'all') {
            $query->where('status', $status);
        }

        // Filter by driver_id if provided
        if (!empty($driverId)) {
            $query->where('driver_id', $driverId);
        }

        // Filter by product_id if provided (search in JSON items array)
        if (!empty($productId)) {
            $query->where(function($q) use ($productId) {
                $q->whereRaw('JSON_CONTAINS(items, \'{"product_id": ' . (int)$productId . '}\', \'$\')');
            });
        }

        // Date range filters
        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Handle DataTable search
        if (request()->has('search') && !empty(request('search')['value'])) {
            $searchTerm = request('search')['value'];
            
            $query->where(function($q) use ($searchTerm) {
                // Search in driver name
                $q->whereHas('driver', function($q2) use ($searchTerm) {
                    $q2->where('name', 'like', '%' . $searchTerm . '%');
                });
                
                // Search in status
                $q->orWhere('status', 'like', '%' . $searchTerm . '%');
                
                // Search in remarks
                $q->orWhere('remarks', 'like', '%' . $searchTerm . '%');
            });
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
                'processing' => true,
                'serverSide' => true,
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
                        'title' => 'Inventory_Returns_' . date('dmYHis'),
                        'filename' => 'Inventory_Returns_' . date('dmYHis')
                    ],
                    [
                        'extend' => 'pdfHtml5',
                        'orientation' => 'landscape',
                        'pageSize' => 'LEGAL',
                        'text' => '<i class="fa fa-file-pdf-o"></i> PDF',
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => 'Inventory_Returns_' . date('dmYHis'),
                        'filename' => 'Inventory_Returns_' . date('dmYHis')
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
                    
                    // Hide loading when DataTable is complete
                    if (typeof HideLoad === "function") {
                        HideLoad();
                    }
                    
                    // Add CSS for column control
                    $("<style>" +
                        "table.dataTable thead th.product-summary-column, " +
                        "table.dataTable tbody td.product-summary-column { " +
                        "   width: 350px !important; " +
                        "   max-width: 350px !important; " +
                        "   min-width: 350px !important; " +
                        "   position: relative;" +
                        "}" +
                        ".product-summary-container { " +
                        "   min-width: 100%; " +
                        "   min-height: 40px;" +
                        "   padding: 5px 0;" +
                        "}" +
                        ".product-summary-container:hover {" +
                        "   background-color: rgba(0,0,0,0.02);" +
                        "}" +
                        "/* Tooltip styling */" +
                        ".tooltip-inner { " +
                        "   max-width: 400px !important;" +
                        "   text-align: left !important;" +
                        "   background-color: #fff !important;" +
                        "   color: #333 !important;" +
                        "   border: 1px solid #ddd !important;" +
                        "   box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;" +
                        "   padding: 12px !important;" +
                        "   border-radius: 4px !important;" +
                        "}" +
                        ".tooltip.show { " +
                        "   opacity: 1 !important;" +
                        "}" +
                        ".tooltip.bs-tooltip-top .arrow::before { " +
                        "   border-top-color: #fff !important;" +
                        "}" +
                        ".tooltip.bs-tooltip-top .arrow { " +
                        "   filter: drop-shadow(0 1px 0 #ddd);" +
                        "}" +
                        "/* Better scrollbar for long tooltips */" +
                        ".tooltip-inner { " +
                        "   max-height: 300px;" +
                        "   overflow-y: auto;" +
                        "}" +
                        ".tooltip-inner::-webkit-scrollbar { " +
                        "   width: 6px;" +
                        "}" +
                        ".tooltip-inner::-webkit-scrollbar-track { " +
                        "   background: #f1f1f1;" +
                        "   border-radius: 3px;" +
                        "}" +
                        ".tooltip-inner::-webkit-scrollbar-thumb { " +
                        "   background: #888;" +
                        "   border-radius: 3px;" +
                        "}" +
                    "</style>").appendTo("head");
                }',
                'drawCallback' => 'function(settings) {
                    // Also hide loading on redraw
                    if (typeof HideLoad === "function") {
                        HideLoad();
                    }
                    
                    // Add data attributes to rows for modal
                    $(".inventory-return-row").each(function() {
                        var rowData = settings.json.data ? settings.json.data[$(this).index()] : null;
                        if(rowData) {
                            $(this).attr("data-return-id", rowData.id);
                            $(this).find(".view-return-btn").attr("data-id", rowData.id);
                            $(this).find(".edit-return-btn").attr("data-id", rowData.id);
                        }
                    });
                    
                    // Destroy existing tooltips first
                    $(\'.product-summary-container\').tooltip("dispose");
                    
                    // Initialize tooltips with better settings
                    $(\'.product-summary-container\').tooltip({
                        placement: "top",
                        container: "body",
                        html: true,
                        trigger: "hover",
                        delay: { "show": 100, "hide": 100 },
                        boundary: "viewport"
                    });
                    
                    // Ensure column width constraints
                    $("td.product-summary-column").css({
                        "width": "350px",
                        "max-width": "350px",
                        "min-width": "350px"
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
                'title' => 'ID',
                'data' => 'id',
                'name' => 'inventory_returns.id',
                'searchable' => false,
                'orderable' => true,
                'visible' => false,
                'width' => '50px'
            ],
            [
                'title' => 'Agent',
                'data' => 'driver_name',
                'name' => 'driver.name',
                'searchable' => true,
                'orderable' => true,
                'width' => '150px'
            ],
            [
                'title' => 'Items',
                'data' => 'item_count',
                'name' => 'item_count',
                'searchable' => false,
                'orderable' => false,
                'width' => '50px',
                'className' => 'text-center'
            ],
            [
                'title' => 'Total Qty',
                'data' => 'total_quantity',
                'name' => 'total_quantity',
                'searchable' => false,
                'orderable' => false,
                'width' => '80px',
                'className' => 'text-center'
            ],
            [
                'title' => 'Products',
                'data' => 'product_summary',
                'name' => 'product_summary',
                'searchable' => false,
                'orderable' => false,
                'width' => '400px',
                'className' => 'product-summary-column'
            ],
            [
                'title' => 'Status',
                'data' => 'status',
                'name' => 'status',
                'searchable' => true,
                'orderable' => true,
                'width' => '100px',
                'className' => 'text-center'
            ],
            [
                'title' => 'Approved By',
                'data' => 'approver_name',
                'name' => 'approver.name',
                'searchable' => true,
                'orderable' => true,
                'width' => '150px',
                'defaultContent' => 'N/A'
            ],
            [
                'title' => 'Remarks',
                'data' => 'remarks',
                'name' => 'remarks',
                'searchable' => true,
                'orderable' => false,
                'width' => '200px'
            ],
            [
                'title' => 'Returned At',
                'data' => 'created_at',
                'name' => 'created_at',
                'searchable' => true,
                'orderable' => true,
                'width' => '120px'
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