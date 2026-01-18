<?php

namespace App\DataTables;

use App\Models\Product;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use App\Models\ProductCategory;

class ProductDataTable extends DataTable
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
            ->addColumn('category_name', function ($product) {
                return $product->category->name ?? 'N/A';
            })
            ->addColumn('status_text', function ($product) {
                return $product->status == 1 ? 'Active' : 'Inactive';
            })
            ->addColumn('price_formatted', function ($product) {
                return number_format($product->price, 2);
            })
            ->addColumn('created_at', function ($product) {
                return $product->created_at ? $product->created_at->format('d/m/Y H:i') : '';
            })
            ->addColumn('action', 'products.datatables_actions')
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Product $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Product $model)
    {
        return $model->newQuery()
            ->with(['category:id,name'])
            ->select('products.*');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        // Get categories for dropdown filter
        $categories = ProductCategory::active()->pluck('name', 'id')->toArray();
        $categoryOptions = json_encode($categories);
        
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->addAction(['title' => trans('products.action'), 'printable' => false])
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => false,
                'order'     => [[1, 'desc']],
                'lengthMenu' => [[ 10, 50, 100, 300 ],[ '10 rows', '50 rows', '100 rows', '300 rows' ]],
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
                        'filename' => 'products_' . date('dmYHis')
                    ],
                    [
                        'extend' => 'pdfHtml5',
                        'orientation' => 'landscape',
                        'pageSize' => 'LEGAL',
                        'text' => '<i class="fa fa-file-pdf-o"></i> ' . trans('table_buttons.pdf'),
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'products_' . date('dmYHis')
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
                        'targets' => -1,
                        'visible' => true
                    ],
                    [
                        'targets' => 0,
                        'visible' => true,
                        'orderable' => false,
                        'searchable' => false,
                        'render' => 'function(data, type){return "<input type=\'checkbox\' class=\'checkboxselect\' checkboxid=\'"+data+"\'/>";}'
                    ],
                    [
                        'targets' => 4, // Category column
                        'render' => 'function(data, type, row, meta){return row.category_name || "N/A";}'
                    ],
                    [
                        'targets' => 5, // Status column
                        'render' => 'function(data, type, row, meta){return row.status_text || (data == 1 ? "Active" : "Inactive");}'
                    ],
                ],
                'initComplete' => 'function(){
                    var columns = this.api().init().columns;
                    var categories = ' . $categoryOptions . ';
                    
                    this.api()
                    .columns()
                    .every(function (index) {
                        var column = this;
                        if(columns[index].searchable){
                            if(columns[index].title == \'Status\'){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="">All</option><option value="1">Active</option><option value="0">Inactive</option></select>\';
                            }else if(columns[index].title == \'Category\'){
                                // Create category dropdown
                                var select = \'<select class="border-0" style="width: 100%;"><option value="">All Categories</option>\';
                                for(var id in categories){
                                    select += \'<option value="\' + categories[id] + \'">\' + categories[id] + \'</option>\';
                                }
                                select += \'</select>\';
                                var input = select;
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
            'checkbox'=> new \Yajra\DataTables\Html\Column([
                'title' => '<input type="checkbox" id="selectallcheckbox">',
                'data' => 'id',
                'name' => 'id',
                'orderable' => false,
                'searchable' => false
            ]),
            // 'id' => new \Yajra\DataTables\Html\Column([
            //     'title' => 'ID',
            //     'data' => 'id',
            //     'name' => 'id'
            // ]),
            'code' => new \Yajra\DataTables\Html\Column([
                'title' => trans('products.code'),
                'data' => 'code',
                'name' => 'code'
            ]),
            'name' => new \Yajra\DataTables\Html\Column([
                'title' => trans('products.name'),
                'data' => 'name',
                'name' => 'name'
            ]),
            'price' => new \Yajra\DataTables\Html\Column([
                'title' => trans('products.price'),
                'data' => 'price_formatted',
                'name' => 'price',
            ]),
            'category_id' => new \Yajra\DataTables\Html\Column([
                'title' => trans('Category'),
                'data' => 'category_name',
                'name' => 'category.name',
                'orderable' => true,
                'searchable' => true
            ]),
            'status' => new \Yajra\DataTables\Html\Column([
                'title' => trans('products.status'),
                'data' => 'status_text',
                'name' => 'status',
                'orderable' => true,
                'searchable' => true
            ]),
            // 'created_at' => new \Yajra\DataTables\Html\Column([
            //     'title' => trans('Created At'),
            //     'data' => 'created_at',
            //     'name' => 'created_at',
            // ])
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'products_datatable_' . time();
    }
}