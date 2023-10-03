<?php

namespace Database\Seeders;

use App\Imports\UsersImport;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Platform\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'System Admin')->first();

        $adminUser = User::find(1);
        $adminUser->addRole($adminRole);

        $csvFile = public_path('imports/users.csv');
        Excel::import(new UsersImport, $csvFile);
    }
}
