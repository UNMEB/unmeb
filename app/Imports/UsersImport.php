<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Orchid\Platform\Models\Role;
use Illuminate\Support\Str;
class UsersImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $user = new User();
        $user->email = Str::of($row['username'] . '@unmeb.go.ug')->replace(' ', '')->lower();
        $user->password = Hash::make('password');
        $user->name =  Str::of($row['name'])->trim()->title();
        $user->username = Str::of($row['username'])
        ->replace(' ', '')
        ->lower();
        $user->institution_id = $row['institution_id'];
        $user->save();

        if ($row['role'] == 'admin_all' || $row['role'] == 'admin_staff' || $row['role'] == 'admin_view') {

            $adminRole = Role::where('name', 'System Admin')->first();
            $user->addRole($adminRole);
        } else if ($row['role'] == 'accounts_all') {
            $accountantRole = Role::where('name', 'Accountant')->first();
            $user->addRole($accountantRole);
        } else if ($row['role'] == 'institution_all' || $row['role'] == 'institution_view') {
            $institutionRole = Role::where('name', 'Institution')->first();
            $user->addRole($institutionRole);
        }

        return $user;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
