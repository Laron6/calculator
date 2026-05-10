<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('group_worker', function (Blueprint $table) {
            $table->unique(['work_group_id', 'worker_id'], 'group_worker_unique');
        });

        Schema::table('group_productivities', function (Blueprint $table) {
            $table->unique(['work_group_id', 'worker_id'], 'group_productivities_unique');
        });

        Schema::table('group_productivities', function (Blueprint $table) {
            $table->decimal('volume', 10, 2)->nullable()->change();
        });

        Schema::table('group_productivities', function (Blueprint $table) {
            $table->decimal('time', 8, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('group_worker', function (Blueprint $table) {
            $table->dropUnique('group_worker_unique');
        });

        Schema::table('group_productivities', function (Blueprint $table) {
            $table->dropUnique('group_productivities_unique');
            $table->integer('volume')->nullable()->change();
            $table->integer('time')->nullable()->change();
        });
    }
};