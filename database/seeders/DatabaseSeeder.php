<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PeriodType;
use App\Enums\UserRole;
use App\Models\DiklatUnit;
use App\Models\Pic;
use App\Models\Profession;
use App\Models\Unit;
use App\Models\User;
use App\Models\WaSetting;
use App\Models\WorkCategory;
use App\Models\WorkPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Master Unit (IT & SIMRS, Rekam Medis)
        $unitIt = Unit::create([
            'code' => 'IT-SIMRS',
            'name' => 'IT & SIMRS',
            'description' => 'Unit Teknologi Informasi & SIMRS.',
            'is_active' => true,
        ]);

        $unitRm = Unit::create([
            'code' => 'RM',
            'name' => 'Rekam Medis',
            'description' => 'Unit Rekam Medis & Informasi Kesehatan.',
            'is_active' => true,
        ]);

        // 2. Single Admin Account (password: "password")
        User::create([
            'employee_id' => 'ADM-001',
            'name' => 'Administrator MedTechLog',
            'email' => 'admin@medtechlog.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'unit_id' => $unitIt->id,
            'position' => 'Administrator',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 3. Master PIC
        Pic::create(['name' => 'Andi Pratama', 'phone' => '081200000001', 'unit_id' => $unitIt->id, 'is_active' => true]);
        Pic::create(['name' => 'Dimas Nugroho', 'phone' => '081200000002', 'unit_id' => $unitIt->id, 'is_active' => true]);
        Pic::create(['name' => 'Siti Rahmawati', 'phone' => '081200000003', 'unit_id' => $unitRm->id, 'is_active' => true]);
        Pic::create(['name' => 'Rina Kusuma', 'phone' => '081200000004', 'unit_id' => $unitRm->id, 'is_active' => true]);

        // 4. Master Unit Diklat (khusus Tugas Diklat, terpisah dari Unit PIC)
        $diklatUnits = [
            ['code' => 'IGD', 'name' => 'Instalasi Gawat Darurat (IGD)'],
            ['code' => 'RI', 'name' => 'Instalasi Rawat Inap'],
            ['code' => 'RJ', 'name' => 'Instalasi Rawat Jalan'],
            ['code' => 'FRM', 'name' => 'Instalasi Farmasi'],
            ['code' => 'LAB', 'name' => 'Laboratorium'],
            ['code' => 'RAD', 'name' => 'Radiologi'],
        ];
        foreach ($diklatUnits as $du) {
            DiklatUnit::create([...$du, 'is_active' => true]);
        }

        // 5. Master Profesi (untuk Tugas Diklat)
        $professions = [
            ['code' => 'DR', 'name' => 'Dokter'],
            ['code' => 'PRW', 'name' => 'Perawat'],
            ['code' => 'BDN', 'name' => 'Bidan'],
            ['code' => 'RMIK', 'name' => 'Perekam Medis'],
            ['code' => 'FRM', 'name' => 'Farmasi / Apoteker'],
            ['code' => 'IT', 'name' => 'Staf IT / SIMRS'],
        ];
        foreach ($professions as $p) {
            Profession::create([...$p, 'is_active' => true]);
        }

        // 5. Internal Default Category & Active Period
        WorkCategory::create(['code' => 'UMUM', 'name' => 'Umum', 'color' => '#0F766E', 'is_active' => true]);

        $now = now();
        WorkPeriod::create([
            'name' => $now->translatedFormat('F Y'),
            'type' => PeriodType::Monthly,
            'start_date' => $now->copy()->startOfMonth(),
            'end_date' => $now->copy()->endOfMonth(),
            'is_active' => true,
        ]);

        // 6. Default WA Manager Setting
        WaSetting::current();
    }
}
