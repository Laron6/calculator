<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('group_productivities', function (Blueprint $table) {
            $table->integer('volume')->nullable()->after('value');
            $table->integer('time')->nullable()->after('volume');
        });
    }

    public function down()
    {
        Schema::table('group_productivities', function (Blueprint $table) {
            $table->dropColumn(['volume', 'time']);
        });
    }
};