<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("work_permit_assignments", function (Blueprint $table) {
            $table->id();

            $table->foreignId("work_permit_id")
                  ->constrained("work_permits")
                  ->restrictOnDelete();

            $table->foreignId("user_id")
                  ->constrained("users")
                  ->restrictOnDelete();

            // assignment_type: requester | approver | release
            // Other types are NEEDS CONFIRMATION — do not add without company confirmation.
            $table->string("assignment_type", 30);

            // Only meaningful for assignment_type = approver.
            // Determines evaluation order (1 = first approver, 2 = second, etc.)
            $table->unsignedTinyInteger("approval_order")->nullable();

            $table->boolean("is_active")->default(true);

            $table->foreignId("assigned_by")
                  ->nullable()
                  ->constrained("users")
                  ->nullOnDelete();

            $table->timestamp("assigned_at")->useCurrent();
            $table->timestamp("unassigned_at")->nullable();

            $table->timestamps();

            $table->index(["work_permit_id", "user_id"]);
            $table->index(["work_permit_id", "assignment_type"]);
            $table->index("is_active");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("work_permit_assignments");
    }
};
