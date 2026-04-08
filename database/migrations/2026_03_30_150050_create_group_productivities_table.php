<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('group_productivities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('worker_id')->constrained()->onDelete('cascade');
            $table->decimal('value', 10, 2);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('group_productivities');
    }
};