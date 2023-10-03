<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Orchid\Platform\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = new Role();
        $adminRole->name = 'System Admin';
        $adminRole->slug = 'system-admin';
        $adminRole->permissions = [
            'platform.index'                                => 1,
            'platform.systems'                              => 1,
            'platform.systems.administration'               => 1,
            'platform.systems.finance.accounts'             => 1,
            'platform.systems.finance.pending'              => 1,
            'platform.systems.finance.complete'             => 1,
            'platform.systems.registration'                 => 1,
            'platform.systems.roles'                        => 1,
            'platform.systems.users'                        => 1,

        ];
        $adminRole->save();

        $institutionRole = new Role();
        $institutionRole->name = 'Institution';
        $institutionRole->slug = 'institution';
        $institutionRole->permissions = [
            'platform.index'                      => 1,
            'platform.systems.continuous-assessment' => 1,
            'platform.systems.registration'       => 1,
            'platform.systems.staff'              => 1,
            'platform.systems.students'           => 1,
            'platform.systems.institution.account_balance' => 1,
            'platform.systems.registration.students' => 1,
            'platform.systems.registration.exams' => 1,
        ];
        $institutionRole->save();

        $accountantRole = new Role();
        $accountantRole->name = 'Accountant';
        $accountantRole->slug = 'accountant';
        $accountantRole->permissions = [
            'platform.index'                      => 1,
            'platform.systems.finance'            => 1,
        ];
        $accountantRole->save();

        $examOfficerRole = new Role();
        $examOfficerRole->name = 'Exam Officer';
        $examOfficerRole->slug = 'exam-officer';
        $examOfficerRole->permissions = [
            'platform.index'                      => 1,
        ];
        $examOfficerRole->save();
    }
}
