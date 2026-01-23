<?php

namespace App\DataTables;

use App\Models\Customer;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class CustomerDataTable extends DataTable
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
            ->addColumn('action', 'customers.datatables_actions')
            ->addColumn('customer_groups', function ($customer) {
                // Get ALL customer groups and filter in PHP (simpler but less efficient for large datasets)
                $allGroups = \App\Models\CustomerGroup::withTrashed() // Include soft deleted if needed
                    ->orderBy('name')
                    ->get();
                
                $customerGroups = collect();
                
                foreach ($allGroups as $group) {
                    $customerIds = $group->customer_ids ?? [];
                    
                    // Check if customer ID exists in the group
                    foreach ($customerIds as $item) {
                        if (isset($item['id']) && $item['id'] == $customer->id) {
                            $customerGroups->push($group->name);
                            break; // No need to check further in this group
                        }
                    }
                }
                
                if ($customerGroups->isEmpty()) {
                    return '-';
                }
                
                return $customerGroups->implode(', ');
            })
            ->filterColumn('customer_groups', function ($query, $keyword) {
                if (!empty($keyword)) {
                    // For filtering, we need to use a subquery
                    $query->whereExists(function ($subQuery) use ($keyword) {
                        $subQuery->select(\DB::raw(1))
                            ->from('customer_groups')
                            ->whereRaw('customer_groups.name LIKE ?', ["%{$keyword}%"])
                            ->whereRaw("(
                                customer_groups.customer_ids LIKE CONCAT('%\"id\":', customers.id, ',%') OR
                                customer_groups.customer_ids LIKE CONCAT('%\"id\":', customers.id, '}%') OR
                                customer_groups.customer_ids LIKE CONCAT('%\"id\": ', customers.id, ',%') OR
                                customer_groups.customer_ids LIKE CONCAT('%\"id\": ', customers.id, '}%')
                            )");
                    });
                }
            })
            ->orderColumn('customer_groups', function ($query, $order) {
                // For ordering, use a subquery with LIKE pattern
                $query->orderBy(
                    \DB::raw("(
                        SELECT GROUP_CONCAT(cg.name ORDER BY cg.name SEPARATOR ', ')
                        FROM customer_groups cg
                        WHERE (
                            cg.customer_ids LIKE CONCAT('%\"id\":', customers.id, ',%') OR
                            cg.customer_ids LIKE CONCAT('%\"id\":', customers.id, '}%') OR
                            cg.customer_ids LIKE CONCAT('%\"id\": ', customers.id, ',%') OR
                            cg.customer_ids LIKE CONCAT('%\"id\": ', customers.id, '}%')
                        )
                    )"),
                    $order
                );
            })
            ->rawColumns(['action', 'customer_groups']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Customer $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Customer $model)
    {
        return $model->newQuery()
            ->select('customers.*');
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
            ->addAction(['title' => 'Action', 'printable' => false])
            ->parameters([
                'dom'       => '<"row"B><"row"<"dataTableBuilderDiv"t>><"row"ip>',
                'stateSave' => true,
                'stateDuration' => 0,
                'processing' => false,
                'order'     => [[1, 'asc']],
                'lengthMenu' => [[10, 50, 100, 300], ['10 rows', '50 rows', '100 rows', '300 rows']],
                'buttons' => [
                    [
                        'extend' => 'create',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-plus"></i> Create',
                    ],
                    [
                        'extend' => 'print',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-print"></i> Print',
                    ],
                    [
                        'extend' => 'reset',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-refresh"></i> Reset',
                    ],
                    [
                        'extend' => 'reload',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-refresh"></i> Reload',
                    ],
                    [
                        'extend' => 'excelHtml5',
                        'text' => '<i class="fa fa-file-excel-o"></i> Excel',
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'customers_' . date('dmYHis')
                    ],
                    [
                        'extend' => 'pdfHtml5',
                        'orientation' => 'landscape',
                        'pageSize' => 'LEGAL',
                        'text' => '<i class="fa fa-file-pdf-o"></i> PDF',
                        'exportOptions' => ['columns' => ':visible:not(:last-child)'],
                        'className' => 'btn btn-default btn-sm no-corner',
                        'title' => null,
                        'filename' => 'customers_' . date('dmYHis')
                    ],
                    [
                        'extend' => 'colvis',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => '<i class="fa fa-columns"></i> Column'
                    ],
                    [
                        'extend' => 'pageLength',
                        'className' => 'btn btn-default btn-sm no-corner',
                        'text' => 'Show 10 rows'
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
                        'render' => 'function(data, type){return "<input type=\'checkbox\' class=\'checkboxselect\' checkboxid=\'"+data+"\'/>";}'
                    ],
                    [
                        'targets' => 5, // Status column index
                        'render' => 'function(data, type){return data == 1 ? "Active" : "Inactive";}'
                    ],
                    [
                        'targets' => 6, // Customer Groups column index
                        'render' => 'function(data, type, row){
                            if (type === "display") {
                                if (!data || data === "-" || data === "") {
                                    return "-";
                                }
                                // Wrap in span with title for full text on hover
                                return "<span title=\'" + data + "\'>" + data + "</span>";
                            }
                            return data;
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
                                var input = \'<select class="border-0" style="width: 100%;"><option value="">All</option><option value="1">Active</option><option value="0">Inactive</option></select>\';
                            } else if(columns[index].title == \'Payment Term\'){
                                var input = \'<select class="border-0" style="width: 100%;"><option value="">All</option><option value="Cash">Cash</option><option value="Credit">Credit</option></select>\';
                            } else if(columns[index].title == \'Customer Groups\'){
                                // Optional: Add autocomplete for customer groups
                                var input = \'<input type="text" placeholder="Search groups..." class="border-0" style="width: 100%;">\';
                            } else {
                                var input = \'<input type="text" placeholder="Search ">\';
                            }
                            $(input).appendTo($(column.footer()).empty()).on(\'change keyup\', function(){
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
            'checkbox' => new \Yajra\DataTables\Html\Column([
                'title' => '<input type="checkbox" id="selectallcheckbox">',
                'data' => 'id',
                'name' => 'id',
                'orderable' => false,
                'searchable' => false
            ]),

            'code' => new \Yajra\DataTables\Html\Column([
                'title' => 'Code',
                'data' => 'code',
                'name' => 'code',
                'width' => '150px' 
            ]),

            'company' => new \Yajra\DataTables\Html\Column([
                'title' => 'Company',
                'data' => 'company',
                'name' => 'company',
                'width' => '300px' 
            ]),

            'paymentterm' => new \Yajra\DataTables\Html\Column([
                'title' => 'Payment Term',
                'data' => 'paymentterm',
                'name' => 'paymentterm',
                'width' => '120px' 
            ]),

            'phone' => new \Yajra\DataTables\Html\Column([
                'title' => 'Phone',
                'data' => 'phone',
                'name' => 'phone',
                'width' => '120px'
            ]),

            'status' => new \Yajra\DataTables\Html\Column([
                'title' => 'Status',
                'data' => 'status',
                'name' => 'status',
                'width' => '80px'
            ]),

            'customer_groups' => new \Yajra\DataTables\Html\Column([
                'title' => 'Customer Groups',
                'data' => 'customer_groups',
                'name' => 'customer_groups',
                'orderable' => true,
                'searchable' => true,
                'width' => '200px'
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
        return 'customers_datatable_' . time();
    }
}