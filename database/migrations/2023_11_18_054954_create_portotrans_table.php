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
        Schema::create('portotrans', function (Blueprint $table) {
            $table->id();
            $table->integer("nominal");
            $table->foreignId("portomember_id");
            $table->foreignId("user_id")->constrained('users');
            $table->string("keterangan");
            $table->enum('status', ['pemasukan', 'pengeluaran']);
            $table->string('foto');
            // $table->foreignId("user_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portotrans');
    }
};
