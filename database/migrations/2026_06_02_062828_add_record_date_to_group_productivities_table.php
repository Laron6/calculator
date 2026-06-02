<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('group_productivities', function (Blueprint $table) {
            $table->date('record_date')->default(DB::raw('CURRENT_DATE'))->after('time');
        });
    }

    public function down()
    {
        Schema::table('group_productivities', function (Blueprint $table) {
            $table->dropColumn('record_date');
        });
    }
};