<?php

namespace App\DataTables;

use App\Models\ProductCategory;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class ProductCategoryDataTable extends DataTable
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

        $dataTable->addColumn('action', function($category) {
            return view('product_categories.datatables_actions', compact('category'))->render();
        });

        // Format status with badge
        $dataTable->editColumn('status', function($category) {
            return '<span class="badge ' . $category->status_badge_class . '">' . $category->status_text . '</span>';
        });

        // Add products count
        $dataTable->addColumn('products_count', function($category) {
            return $category->products_count ?? $category->products()->count();
        });

        // Format dates
        $dataTable->editColumn('created_at', function($category) {
            return $category->created_at ? $category->created_at->format('d-m-Y H:i') : '';
        });

        $dataTable->editColumn('updated_at', function($category) {
            return $category->updated_at ? $category->updated_at->format('d-m-Y H:i') : '';
        });

        return $dataTable->rawColumns(['action', 'status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ProductCategory $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ProductCategory $model)
    {
        $query = $model->newQuery()
            ->withCount('products')
            ->select('product_categories.*');

        // Filter by status if requested
        if ($this->request()->has('status') && $this->request()->status != 'all') {
            $query->where('status', $this->request()->status);
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
                'order'     => [[0, 'asc']],
                'lengthMenu' => [[10, 50, 100, 300], ['10 rows', '50 rows', '100 rows', '300 rows']],
                'buttons' => [
                    [
                        'extend' => 'create',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-plus"></i> Create',
                        'action' => 'function(e, dt, node, config) {
                            $("#createCategory").modal("show");
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
                        'title' => 'Product_Categories_' . date('dmYHis'),
                        'filename' => 'Product_Categories_' . date('dmYHis')
                    ],
                    [
                        'extend' => 'pdfHtml5',
                        'orientation' => 'landscape',
                        'pageSize' => 'LEGAL',
                        'text' => '<i class="fa fa-file-pdf-o"></i> PDF',
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => 'Product_Categories_' . date('dmYHis'),
                        'filename' => 'Product_Categories_' . date('dmYHis')
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
                                var input = \'<select class="border-0" style="width: 100%;"><option value="">All</option><option value="1">Active</option><option value="0">Inactive</option></select>\';
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
                'title' => 'ID',
                'data' => 'id',
                'name' => 'id',
                'searchable' => false,
                'orderable' => true,
                'width' => '50px'
            ],
            [
                'title' => 'Name',
                'data' => 'name',
                'name' => 'name',
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
                'title' => 'Products Count',
                'data' => 'products_count',
                'name' => 'products_count',
                'searchable' => false,
                'orderable' => true
            ],
            [
                'title' => 'Created At',
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
        return 'product_categories_datatable_' . time();
    }
}