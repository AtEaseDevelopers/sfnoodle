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

        return $dataTable
           ->editColumn('uuid', function ($row) {
                // Add "T-" prefix to UUID
                return 'T-' . $row->uuid;
            })
            ->addColumn('action', function ($row) {
                if ($row->type == 0) {
                    return '<div class="btn-group">
                        <a href="' . route('tripsummaries', $row->uuid) . '" 
                        target="_blank" 
                        class="btn btn-ghost-success" 
                        title="View Trip Summary Report"
                        data-toggle="tooltip">
                            <i class="fa fa-print"></i>
                        </a>

                        <a href="' . route('trips.stockCount', ['driver_id' => $row->driver_id, 'trip_id' => $row->uuid]) . '" 
                            class="btn btn-ghost-info" 
                            title="View Stock Count Report"
                            data-toggle="tooltip"
                            target="_blank">
                            <i class="fa fa-file-archive-o"></i>
                        </a>
                    </div>';
                } else {
                    return '';
                }
            })
            ->editColumn('type', function ($row) {
                if ($row->type == 1) {
                    // Start Trip - Orange color
                    return '<span class="badge" style="background-color: #ff9800; color: dark; padding: 5px 10px; border-radius: 4px;">
                        </i> Start Trip
                    </span>';
                } else {
                    // End Trip - Green color
                    return '<span class="badge" style="background-color: #7bdf7eff; color: dark; padding: 5px 10px; border-radius: 4px;">
                        </i> End Trip
                    </span>';
                }
            })
            ->rawColumns(['action', 'type']); // Add 'type' to rawColumns
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
            ->addAction(['title' => trans('trips.action'), 'printable' => false])
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => false,
                'order'     => [[1, 'desc']],
                'lengthMenu' => [[ 10, 50, 100, 300 ],[ '10 rows', '50 rows', '100 rows', '300 rows' ]],
                'buttons' => [
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
                'columnDefs' => [
                    [
                        'targets' => 3, // Type column index
                        'render' => 'function(data, type){ 
                            if(type === "display" || type === "filter") {
                                return data;
                            }
                            return data == 1 ? "Start Trip" : "End Trip";
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
                            if(columns[index].title == \'Type\'){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="">All</option><option value="1">Start Trip</option><option value="2">End Trip</option></select>\';
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
            'uuid'=> new \Yajra\DataTables\Html\Column([
                'title' => trans('trips.trip_id'),
                'data' => 'uuid',
                'name' => 'uuid',
                
            ]),

            'date',

            'driver_id'=> new \Yajra\DataTables\Html\Column([
                'title' => trans('trips.driver'),
                'data' => 'driver.name',
                'name' => 'driver.name'
            ]),

            'type'=> new \Yajra\DataTables\Html\Column([
                'title' => trans('trips.type'),
                'data' => 'type',
                'name' => 'type'
            ]),
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