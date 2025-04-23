<?php

namespace App\DataTables;

use App\Models\Task;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class TaskDataTable extends DataTable
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

        return $dataTable->addColumn('action', 'tasks.datatables_actions');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Task $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Task $model)
    {
        return $model->newQuery()
        ->with('invoice:id,invoiceno')
        ->with('customer:id,company')
        ->with('driver:id,name')
        ->select('tasks.*');
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
                    ['extend' => 'excelHtml5','text'=>'<i class="fa fa-file-excel-o"></i> Excel','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'task'.date('dmYHis')],
                    ['extend' => 'pdfHtml5', 'orientation' => 'landscape', 'pageSize' => 'LEGAL','text'=>'<i class="fa fa-file-pdf-o"></i> PDF','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'task'.date('dmYHis')],
                    ['extend' => 'colvis', 'className' => 'btn btn-default btn-sm no-corner','text'=>'<i class="fa fa-columns"></i> Column',],
                    ['extend' => 'pageLength','className' => 'btn btn-default btn-sm no-corner',],
                ],
                'columnDefs' => [
                    [
                        'targets' => -1,
                        'visible' => true,
                        'className' => 'dt-body-right'
                    ],
                    [
                        'targets' => 0,
                        'visible' => true,
                        'render' => 'function(data, type){return "<input type=\'checkbox\' class=\'checkboxselect\' checkboxid=\'"+data+"\'/>";}'
                    ],
                    [
                        'targets' => 7,
                        'render' => 'function(data, type){
                                                            if(data == 0){
                                                                return "New";
                                                            }
                                                            if(data == 1){
                                                                return "In-Progress";
                                                            }
                                                            if(data == 8){
                                                                return "Completed";
                                                            }
                                                            if(data == 9){
                                                                return "Cancelled";
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
                            if(columns[index].title == \'Status\'){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="8">Completed</option><option value="0">New</option><option value="1">In-Progress</option><option value="9">Cancelled</option></select>\';
                            }else if(columns[index].title == \'Type\'){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="1">Cash</option><option value="2">Bankin</option></select>\';
                            }else if(columns[index].title == \'Approve At\'){
                                var input = \'<input type="text" id="\'+index+\'Date" onclick="searchDateColumn(this);" placeholder="Search ">\';
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
            'searchable' => false]),

            'trip_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Trip ID',
            'data' => 'trip_id',
            'name' => 'trip_id']),

            'date',

            'driver_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Driver',
            'data' => 'driver.name',
            'name' => 'driver.name']),

            'customer_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Customer',
            'data' => 'customer.company',
            'name' => 'customer.company']),

            'sequence',

            'invoice_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Invoice No',
            'data' => 'invoice.invoiceno',
            'name' => 'invoice.invoiceno']),

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
        return 'tasks_datatable_' . time();
    }
}
