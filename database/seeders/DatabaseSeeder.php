<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Department;
use App\Models\PermitType;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Departments ────────────────────────────────────────────────────
        $departments = collect([
            ["name" => "Engineering",      "code" => "ENG"],
            ["name" => "HSE",              "code" => "HSE"],
            ["name" => "Operations",       "code" => "OPS"],
            ["name" => "Maintenance",      "code" => "MNT"],
            ["name" => "Administration",   "code" => "ADM"],
        ])->map(fn($d) => Department::firstOrCreate(["code" => $d["code"]], $d));

        // ── Plants & Buildings (from screenshot evidence) ──────────────────
        $plants = [
            "NGBC"          => ["NGBC"],
            "RWT/WWT Plant" => ["Influent", "Effluent"],
            "Power Plant"   => ["Belt Press", "Generator Room"],
            "Spinning"      => ["Dryer", "Bal Press"],
            "S.A.P.R"       => ["MSPS", "Ab Station"],
        ];

        foreach ($plants as $plantName => $buildings) {
            $plant = Plant::firstOrCreate(["name" => $plantName], [
                "code"      => strtoupper(substr(preg_replace("/[^A-Za-z]/", "", $plantName), 0, 5)),
                "is_active" => true,
            ]);
            foreach ($buildings as $bName) {
                Building::firstOrCreate(
                    ["plant_id" => $plant->id, "name" => $bName],
                    ["is_active" => true]
                );
            }
        }

        // ── Permit Types ───────────────────────────────────────────────────
        // NEEDS CONFIRMATION: actual permit type list from company.
        // These are placeholders only.
        collect([
            ["name" => "Hot Work",        "code" => "HOT"],
            ["name" => "Cold Work",       "code" => "COLD"],
            ["name" => "Confined Space",  "code" => "CS"],
            ["name" => "Electrical Work", "code" => "ELEC"],
            ["name" => "Working at Height","code" => "WAH"],
        ])->each(fn($t) => PermitType::firstOrCreate(["code" => $t["code"]], $t));

        // ── Development Users ─────────────────────────────────────────────
        // These users are ONLY for AUTH_MODE=development.
        // Never create real company accounts here.
        if (config("auth.mode") === "development" || app()->environment("local")) {
            $eng  = $departments->firstWhere("code", "ENG");
            $hse  = $departments->firstWhere("code", "HSE");
            $ops  = $departments->firstWhere("code", "OPS");
            $adm  = $departments->firstWhere("code", "ADM");

            // Admin
            User::firstOrCreate(["username" => "admin", "domain" => "LAGGRF"], [
                "name"            => "Administrator",
                "employee_id"     => "ADM-0001",
                "upn"             => "admin@pt-spv.com",
                "email"           => "admin@pt-spv.com",
                "department_id"   => $adm->id,
                "position"        => "System Administrator",
                "is_active"       => true,
                "is_global_admin" => true,
            ]);

            // Requester
            User::firstOrCreate(["username" => "spvtrain1", "domain" => "LAGGRF"], [
                "name"          => "SPV Training 1",
                "employee_id"   => "ENG-0001",
                "upn"           => "spvtraining1@pt-spv.com",
                "email"         => "spvtraining1@pt-spv.com",
                "department_id" => $eng->id,
                "position"      => "Supervisor",
                "is_active"     => true,
            ]);

            // Approver
            User::firstOrCreate(["username" => "andi", "domain" => "LAGGRF"], [
                "name"          => "Andi Wirawan",
                "employee_id"   => "HSE-0001",
                "upn"           => "andi.wirawan@pt-spv.com",
                "email"         => "andi.wirawan@pt-spv.com",
                "department_id" => $hse->id,
                "position"      => "HSE Officer",
                "is_active"     => true,
            ]);

            // Released By
            User::firstOrCreate(["username" => "budi", "domain" => "LAGGRF"], [
                "name"          => "Budi Santoso",
                "employee_id"   => "OPS-0001",
                "upn"           => "budi.santoso@pt-spv.com",
                "email"         => "budi.santoso@pt-spv.com",
                "department_id" => $ops->id,
                "position"      => "Foreman",
                "is_active"     => true,
            ]);
        }
    }
}
