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
        Schema::connection('mongodb')->create('jobs', function (Blueprint $collection) {
            $collection->bigIncrements('id');
            $collection->string('queue')->index();
            $collection->longText('payload');
            $collection->unsignedTinyInteger('attempts');
            $collection->unsignedInteger('reserved_at')->nullable();
            $collection->unsignedInteger('available_at');
            $collection->unsignedInteger('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
