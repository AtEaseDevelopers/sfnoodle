<?php

namespace App\DataTables;

use App\Models\foc;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class focDataTable extends DataTable
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

        return $dataTable->addColumn('action', 'focs.datatables_actions');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\foc $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(foc $model)
    {
        return $model->newQuery()
        ->with('product:id,name')
        ->with('customer:id,company')
        ->with('freeproduct:id,name')
        ->select('focs.*');
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
            ->addAction(['width' => '120px', 'printable' => false])
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => false,
                'order'     => [[1, 'desc']],
                'lengthMenu' => [[ 10, 50, 100, 300 ],[ '10 rows', '50 rows', '100 rows', '300 rows' ]],
                'buttons'   => [
                    ['extend' => 'create', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'print', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'reset', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'reload', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'excelHtml5','text'=>'<i class="fa fa-file-excel-o"></i> Excel','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'foc'.date('dmYHis')],
                    ['extend' => 'pdfHtml5', 'orientation' => 'landscape', 'pageSize' => 'LEGAL','text'=>'<i class="fa fa-file-pdf-o"></i> PDF','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'foc'.date('dmYHis')],
                    ['extend' => 'colvis', 'className' => 'btn btn-default btn-sm no-corner','text'=>'<i class="fa fa-columns"></i> Column',],
                    ['extend' => 'pageLength','className' => 'btn btn-default btn-sm no-corner',],
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
                    'targets' => 9,
                    'render' => 'function(data, type){return data == 1 ? "Active" : "Unactive";}'],
                ],
                'initComplete' => 'function(){
                    var columns = this.api().init().columns;
                    this.api()
                    .columns()
                    .every(function (index) {
                        var column = this;
                        if(columns[index].searchable){
                            if(columns[index].title == \'Status\'){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="1">Active</option><option value="0">Unactive</option></select>\';
                            }else if(columns[index].title == \'Payment Term\'){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="1">Cash</option><option value="2">Bankin</option><option value="3">Credit Note</option></select>\';
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
            'searchable' => false]),

            'product_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Product',
            'data' => 'product.name',
            'name' => 'product.name']),

            'customer_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Customer',
            'data' => 'customer.company',
            'name' => 'customer.company']),

            'achievequantity'=> new \Yajra\DataTables\Html\Column(['title' => 'Achieve Quantity',
            'data' => 'achievequantity',
            'name' => 'achievequantity']),

            'quantity',

            'free_product_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Free Product',
            'data' => 'freeproduct.name',
            'name' => 'freeproduct.name']),

            'free_quantity'=> new \Yajra\DataTables\Html\Column(['title' => 'Free Quantity',
            'data' => 'free_quantity',
            'name' => 'free_quantity']),

            'startdate'=> new \Yajra\DataTables\Html\Column(['title' => 'Start Date',
            'data' => 'startdate',
            'name' => 'startdate']),

            'enddate'=> new \Yajra\DataTables\Html\Column(['title' => 'End Date',
            'data' => 'enddate',
            'name' => 'enddate']),

            'status'
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'focs_datatable_' . time();
    }
}
