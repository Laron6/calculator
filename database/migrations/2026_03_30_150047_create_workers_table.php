<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('patronymic')->nullable();
            $table->integer('age');
            $table->integer('experience');
            $table->tinyInteger('gender');
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('workers');
    }
};