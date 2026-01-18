<?php

namespace App\DataTables;

use App\Models\Assign;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class AssignDataTable extends DataTable
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
            ->addColumn('customer_count', function ($assign) {
                if ($assign->customerGroup && $assign->customerGroup->customer_ids) {
                    return count($assign->customerGroup->customer_ids);
                }
                return 0;
            })
             ->addColumn('created_at', function ($customerGroup) {
                return $customerGroup->created_at ? $customerGroup->created_at->format('d/m/Y H:i') : '';
            })
            ->addColumn('action', 'assigns.datatables_actions')
            ->rawColumns(['action', 'checkbox']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Assign $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Assign $model)
    {
        return $model->newQuery()
            ->with(['driver:id,name', 'customerGroup:id,name'])
            ->select('assigns.*');
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
            ->addAction(['title' => trans('assign.action'), 'printable' => false])
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
                        'filename' => 'assignments_' . date('dmYHis')
                    ],
                    [
                        'extend' => 'pdfHtml5',
                        'orientation' => 'landscape',
                        'pageSize' => 'LEGAL',
                        'text' => '<i class="fa fa-file-pdf-o"></i> ' . trans('table_buttons.pdf'),
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'assignments_' . date('dmYHis')
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
                        'render' => 'function(data, type, row, meta){return "<input type=\'checkbox\' class=\'checkboxselect\' checkboxid=\'"+data+"\'/>";}'
                    ],
                    [
                        'targets' => 4, // sequence column
                        'visible' => false,
                        'searchable' => false
                    ]
                ],
                'initComplete' => 'function(){
                    var columns = this.api().init().columns;
                    this.api()
                    .columns()
                    .every(function (index) {
                        var column = this;
                        if(columns[index].searchable){
                            var input = \'<input type="text" placeholder="Search">\';
                            $(input).appendTo($(column.footer()).empty()).on(\'change\', function(){
                                column.search($(this).val(),true,false).draw();
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
            'checkbox' => new \Yajra\DataTables\Html\Column([
                'title' => '<input type="checkbox" id="selectallcheckbox">',
                'data' => 'id',
                'name' => 'id',
                'orderable' => false,
                'searchable' => false
            ]),
            'id' => new \Yajra\DataTables\Html\Column([
                'title' => 'ID',
                'data' => 'id',
                'name' => 'id',
                'visible' => false
            ]),
            'driver.name' => new \Yajra\DataTables\Html\Column([
                'title' => 'Agents',
                'data' => 'driver.name',
                'name' => 'driver.name'
            ]),
            'customer_group.name' => new \Yajra\DataTables\Html\Column([
                'title' => trans('Customer Group'),
                'data' => 'customer_group.name',
                'name' => 'customerGroup.name'
            ]),
            // 'sequence' => new \Yajra\DataTables\Html\Column([
            //     'title' => trans('assign.sequence'),
            //     'data' => 'sequence',
            //     'name' => 'sequence',
            //     'visible' => false
            // ]),
            'customer_count' => new \Yajra\DataTables\Html\Column([
                'title' => trans('Customer Count'),
                'data' => 'customer_count',
                'name' => 'customer_count',
                'orderable' => false,
                'searchable' => false
            ]),
             'created_at' => new \Yajra\DataTables\Html\Column([
                'title' => trans('Created At'),
                'data' => 'created_at',
                'name' => 'created_at',
                'searchable' => false
            ])
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'assigns_datatable_' . time();
    }
}