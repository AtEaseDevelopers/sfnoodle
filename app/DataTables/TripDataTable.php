<?php

namespace App\DataTables;

use App\Models\Trip;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;
use Illuminate\Support\Facades\Crypt;

class TripDataTable extends DataTable
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

      return $dataTable->addColumn('action', function ($row) {
        if ($row->type == 2) {
            return '<div class="btn-group">
                        <a href="' . route('trips.show', Crypt::encrypt($row->id)) . '" class="btn btn-ghost-success">
                            <i class="fa fa-eye"></i>
                        </a>
                    </div>';
        } else {
            return '';
        }
    })
    ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Trip $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Trip $model)
    {
        return $model->newQuery()
        ->with('driver:id,name')
        ->with('kelindan:id,name')
        ->with('lorry:id,lorryno')
        ->select('trips.*');
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
                    // ['extend' => 'create', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'print', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'reset', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'reload', 'className' => 'btn btn-default btn-sm no-corner',],
                    ['extend' => 'excelHtml5','text'=>'<i class="fa fa-file-excel-o"></i> Excel','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'trip'.date('dmYHis')],
                    ['extend' => 'pdfHtml5', 'orientation' => 'landscape', 'pageSize' => 'LEGAL','text'=>'<i class="fa fa-file-pdf-o"></i> PDF','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'trip'.date('dmYHis')],
                    ['extend' => 'colvis', 'className' => 'btn btn-default btn-sm no-corner','text'=>'<i class="fa fa-columns"></i> Column',],
                    ['extend' => 'pageLength','className' => 'btn btn-default btn-sm no-corner',],
                ],
                'columnDefs' => [
                    // [
                    //     'targets' => -1,
                    //     'visible' => true,
                    //     'className' => 'dt-body-right'
                    // ],
                    // [
                    //     'targets' => 0,
                    //     'visible' => true,
                    //     'render' => 'function(data, type){return "<input type=\'checkbox\' class=\'checkboxselect\' checkboxid=\'"+data+"\'/>";}'
                    // ],
                    [
                        'targets' => 6,
                        'render' => 'function(data, type){return data == 1 ? "Start Trip" : "End Trip";}'
                    ],
                    
                    [
                        'targets' => 6,
                        'render' => 'function(data, type){return data == 1 ? "Start Trip" : "End Trip";}'
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
                                var input = \'<select class="border-0" style="width: 100%;"><option value="1">Start Trip</option><option value="2">End Trip</option></select>\';
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
            'id'=> new \Yajra\DataTables\Html\Column(['title' => 'Trip ID',
            'data' => 'id',
            'name' => 'id']),

            'date',

            'driver_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Driver',
            'data' => 'driver.name',
            'name' => 'driver.name']),

            'kelindan_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Kelindan',
            'data' => 'kelindan.name',
            'name' => 'kelindan.name']),

            'lorry_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Lorry',
            'data' => 'lorry.lorryno',
            'name' => 'lorry.lorryno']),

            'cash'=> new \Yajra\DataTables\Html\Column(['title' => 'Closing Cash',
            'data' => 'cash',
            'name' => 'cash']),


            'type'
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'trips_datatable_' . time();
    }
}
