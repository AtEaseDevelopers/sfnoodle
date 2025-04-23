<?php

namespace App\DataTables;

use App\Models\servicedetails;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class servicedetailsDataTable extends DataTable
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

        return $dataTable->addColumn('action', 'servicedetails.datatables_actions');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\servicedetails $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(servicedetails $model)
    {
        return $model->newQuery()->with('lorry:id,lorryno')->select('servicedetails.*');;
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
                    ['extend' => 'excelHtml5','text'=>'<i class="fa fa-file-excel-o"></i> Excel','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'LorryService'.date('dmYHis')],
                    ['extend' => 'pdfHtml5', 'orientation' => 'landscape', 'pageSize' => 'LEGAL','text'=>'<i class="fa fa-file-pdf-o"></i> PDF','exportOptions'=> ['columns'=>':visible:not(:last-child)'], 'className' => 'btn btn-default btn-sm no-corner','title'=>null,'filename'=>'LorryService'.date('dmYHis')],
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
                        'targets' => 5,
                        'render' => 'function(data, type){return parseFloat(data).toFixed(2).split(".")[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") + (parseFloat(data).toFixed(2).split(".")[1] ? "." + parseFloat(data).toFixed(2).split(".")[1] : "");}'
                        ,'className' => 'dt-body-right'
                    ],
                    [
                    'targets' => 7,
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
                            }else if(columns[index].title == \'Date\'){
                                var input = \'<input type="text" id="\'+index+\'Date" onclick="searchDateColumn(this);" placeholder="Search ">\';
                            }else if(columns[index].title == \'Next Date\'){
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
            'lorry_id'=> new \Yajra\DataTables\Html\Column(['title' => 'Lorry',
            'data' => 'lorry.lorryno',
            'name' => 'lorry.lorryno']),
            'type'=> new \Yajra\DataTables\Html\Column(['title' => 'Service Type',
            'data' => 'type',
            'name' => 'servicedetails.type']),
            'date',
            'nextdate'=> new \Yajra\DataTables\Html\Column(['title' => 'Next Date',
            'data' => 'nextdate',
            'name' => 'servicedetails.nextdate']),
            'amount',
            'remark'
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'servicedetails_datatable_' . time();
    }
}
