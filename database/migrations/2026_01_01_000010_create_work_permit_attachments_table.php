<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("work_permit_attachments", function (Blueprint $table) {
            $table->id();

            $table->foreignId("work_permit_id")
                  ->constrained("work_permits")
                  ->restrictOnDelete();

            $table->foreignId("uploaded_by")
                  ->nullable()
                  ->constrained("users")
                  ->nullOnDelete();

            $table->string("file_name", 255);

            // Stored outside public/ — served only through authorized controller.
            $table->string("file_path", 500);

            $table->string("mime_type", 100)->nullable();
            $table->unsignedInteger("file_size")->nullable();

            // attachment_type categories are NEEDS CONFIRMATION.
            // Column reserved for future use — nullable for now.
            $table->string("attachment_type", 50)->nullable();

            $table->timestamps();

            $table->index("work_permit_id");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("work_permit_attachments");
    }
};
