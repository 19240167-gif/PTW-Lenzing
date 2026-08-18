<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("users", function (Blueprint $table) {
            $table->id();
            $table->string("employee_id", 100)->nullable();
            $table->string("domain", 100)->nullable();
            $table->string("username", 150)->nullable();
            $table->string("upn", 255)->nullable();
            $table->string("email", 255)->nullable();
            $table->string("name", 255);
            $table->foreignId("department_id")->nullable()->constrained("departments")->nullOnDelete();
            $table->string("position", 150)->nullable();
            $table->boolean("is_active")->default(true);
            $table->boolean("is_global_admin")->default(false);
            $table->rememberToken();
            $table->timestamps();

            // Windows identity: same username allowed across different domains
            $table->unique(["domain", "username"], "users_domain_username_unique");

            // UPN unique where not null (handled at app layer for nullable unique)
            $table->unique("upn");

            // Email unique where not null
            $table->unique("email");

            $table->index("department_id");
            $table->index("is_active");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("users");
    }
};
