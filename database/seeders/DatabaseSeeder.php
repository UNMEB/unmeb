<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MediaManager;
use App\Models\Surcharge;
use Database\Seeders\Api\ApiDatabaseSeeder;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->call([PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            InstitutionSeeder::class,
            DistrictSeeder::class,
            CourseSeeder::class,
            PaperSeeder::class,
            StudentSeeder::class,
            YearSeeder::class,
            SurchargeSeeder::class,
            SurchargeFeeSeeder::class,
            NsinRegistrationSeeder::class,
        ]);
    }
}