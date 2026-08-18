<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("permit_number_sequences", function (Blueprint $table) {
            $table->id();
            // scope_key: e.g. "2026" (per-year global).
            // NEEDS CONFIRMATION: whether scope should be per-year+building.
            $table->string("scope_key", 100)->unique();
            $table->unsignedInteger("last_number")->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("permit_number_sequences");
    }
};
