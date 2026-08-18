<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("buildings", function (Blueprint $table) {
            $table->id();
            $table->foreignId("plant_id")->constrained("plants")->restrictOnDelete();
            $table->string("name", 150);
            $table->string("code", 50)->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamps();

            $table->index("plant_id");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("buildings");
    }
};
