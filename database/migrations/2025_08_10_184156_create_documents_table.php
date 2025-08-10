<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('original_filename');
            $table->string('file_path');
            $table->bigInteger('file_size');
            $table->string('mime_type');
            $table->string('category')->default('general');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamp('upload_date')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'upload_date']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
