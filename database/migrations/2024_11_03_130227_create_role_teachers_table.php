<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId("role_id")->constrained(
                table: 'roles', indexName: 'role_teacher_role_id'
            );
            $table->foreignId("teacher_id")->constrained(
                table: 'teachers', indexName: 'role_teacher_teacher_id'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_teachers');
    }
};