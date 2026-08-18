<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("work_permit_approvals", function (Blueprint $table) {
            $table->id();

            $table->foreignId("work_permit_id")
                  ->constrained("work_permits")
                  ->restrictOnDelete();

            // Snapshot of the approver at the time the approval record was created.
            // This preserves history even if assignments change later.
            $table->foreignId("approver_id")
                  ->constrained("users")
                  ->restrictOnDelete();

            // Snapshot of the approval_order at cycle creation time.
            $table->unsignedTinyInteger("approval_order");

            // Increments on each revision cycle. History from prior cycles is preserved.
            $table->unsignedTinyInteger("cycle_number")->default(1);

            // status: pending | approved | rejected | skipped
            $table->string("status", 20)->default("pending");

            $table->text("comment")->nullable();
            $table->timestamp("approved_at")->nullable();
            $table->timestamp("rejected_at")->nullable();

            $table->timestamps();

            // One record per approver per order per cycle
            $table->unique(["work_permit_id", "approval_order", "cycle_number"], "wpa_permit_order_cycle_unique");

            $table->index(["approver_id", "status"]);
            $table->index("work_permit_id");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("work_permit_approvals");
    }
};
