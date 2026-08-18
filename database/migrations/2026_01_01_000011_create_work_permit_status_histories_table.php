<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("work_permit_status_histories", function (Blueprint $table) {
            $table->id();

            $table->foreignId("work_permit_id")
                  ->constrained("work_permits")
                  ->restrictOnDelete();

            // NULL for the initial creation entry
            $table->string("from_status", 30)->nullable();
            $table->string("to_status", 30);

            $table->foreignId("changed_by")
                  ->nullable()
                  ->constrained("users")
                  ->nullOnDelete();

            $table->text("comment")->nullable();

            // Append-only: no updated_at, no soft delete
            $table->timestamp("created_at")->useCurrent();

            $table->index("work_permit_id");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("work_permit_status_histories");
    }
};
