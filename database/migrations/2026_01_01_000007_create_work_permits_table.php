<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("work_permits", function (Blueprint $table) {
            $table->id();
            $table->string("permit_number", 50)->unique();
            $table->string("title", 255);
            $table->text("description")->nullable();

            $table->foreignId("permit_type_id")
                  ->nullable()
                  ->constrained("permit_types")
                  ->nullOnDelete();

            $table->foreignId("plant_id")
                  ->nullable()
                  ->constrained("plants")
                  ->nullOnDelete();

            $table->foreignId("building_id")
                  ->nullable()
                  ->constrained("buildings")
                  ->nullOnDelete();

            $table->foreignId("requester_id")
                  ->nullable()
                  ->constrained("users")
                  ->nullOnDelete();

            // Status stored as VARCHAR — not MySQL ENUM — for flexibility.
            // Controlled exclusively by WorkPermitWorkflowService.
            $table->string("status", 30)->default("draft");

            $table->dateTime("valid_from")->nullable();
            $table->dateTime("valid_until")->nullable();

            $table->foreignId("created_by")
                  ->nullable()
                  ->constrained("users")
                  ->nullOnDelete();

            $table->foreignId("updated_by")
                  ->nullable()
                  ->constrained("users")
                  ->nullOnDelete();

            $table->timestamps();

            // Soft delete — only DRAFT permits may be soft-deleted.
            $table->softDeletes();

            $table->index("status");
            $table->index("requester_id");
            $table->index("plant_id");
            $table->index("building_id");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("work_permits");
    }
};
