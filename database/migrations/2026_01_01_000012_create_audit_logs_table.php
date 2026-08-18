<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("audit_logs", function (Blueprint $table) {
            $table->id();

            // Nullable: system-generated actions have no user
            $table->foreignId("user_id")
                  ->nullable()
                  ->constrained("users")
                  ->nullOnDelete();

            $table->string("action", 80);        // e.g. CREATE_PERMIT, APPROVE, REASSIGN
            $table->string("record_type", 80);   // e.g. WorkPermit, WorkPermitApproval
            $table->unsignedBigInteger("record_id")->nullable();
            $table->string("field_name", 100)->nullable();
            $table->text("old_value")->nullable();
            $table->text("new_value")->nullable();
            $table->string("ip_address", 45)->nullable();
            $table->text("user_agent")->nullable();

            // Append-only: no updated_at, no soft delete, no application-level delete
            $table->timestamp("created_at")->useCurrent();

            $table->index("user_id");
            $table->index(["record_type", "record_id"]);
            $table->index("created_at");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("audit_logs");
    }
};
