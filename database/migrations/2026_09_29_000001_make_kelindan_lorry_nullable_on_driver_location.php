<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeKelindanLorryNullableOnDriverLocation extends Migration
{
    /**
     * Trips no longer record kelindan/lorry, so driver_location inserts these
     * as null. Relax the NOT NULL constraint, preserving each column's type.
     */
    public function up()
    {
        foreach (['kelindan_id', 'lorry_id'] as $column) {
            $col = DB::select("SHOW COLUMNS FROM driver_location LIKE '{$column}'");
            if (!empty($col)) {
                DB::statement("ALTER TABLE driver_location MODIFY `{$column}` {$col[0]->Type} NULL");
            }
        }
    }

    public function down()
    {
        foreach (['kelindan_id', 'lorry_id'] as $column) {
            $col = DB::select("SHOW COLUMNS FROM driver_location LIKE '{$column}'");
            if (!empty($col)) {
                DB::statement("ALTER TABLE driver_location MODIFY `{$column}` {$col[0]->Type} NOT NULL");
            }
        }
    }
}
