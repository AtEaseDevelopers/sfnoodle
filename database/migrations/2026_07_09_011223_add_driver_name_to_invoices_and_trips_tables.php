<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDriverNameToInvoicesAndTripsTables extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('driver_name')->nullable()->after('driver_id');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->string('driver_name')->nullable()->after('driver_id');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('driver_name');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('driver_name');
        });
    }
}
