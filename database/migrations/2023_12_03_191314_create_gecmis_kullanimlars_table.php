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
        Schema::create('gecmis_kullanimlars', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('feeder_id');
            $table->integer('adet');
            $table->string('tutar');
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gecmis_kullanimlars');
    }
};
