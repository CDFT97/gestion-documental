<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dynamic_table_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dynamic_table_id')->constrained()->onDelete('cascade');
            $table->json('data'); 
            $table->integer('row_number'); 
            $table->timestamps();

            $table->index(['dynamic_table_id', 'row_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dynamic_table_records');
    }
};
