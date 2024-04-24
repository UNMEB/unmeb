<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchid\Platform\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the required roles
        $adminRole = new Role();
        $adminRole->name = 'System Admin';
        $adminRole->slug = 'administrator';
        $adminRole->permissions = [
            'platform.index'                                                    => 1,
        ];
        $adminRole->save();

        $institutionRole = new Role();
        $institutionRole->name = 'Institution';
        $institutionRole->slug = 'institution';
        $institutionRole->permissions = [
                        'platform.index'                      => 1,
        ];
        $institutionRole->save();

        $accountantRole = new Role();
        $accountantRole->name = 'Accountant';
        $accountantRole->slug = 'accountant';
        $accountantRole->permissions = [
                        'platform.index'                      => 1,
            'platform.index'                      => 1,
        ];
        $accountantRole->save();

        $examOfficerRole = new Role();
        $examOfficerRole->name = 'Exam Officer';
        $examOfficerRole->slug = 'exam-officer';
        $examOfficerRole->permissions = [
                        'platform.index'                      => 1,
        ];
        $examOfficerRole->save();

        DB::table('users')->chunkById(100, function ($users) use ($adminRole, $institutionRole, $accountantRole, $examOfficerRole) {
            foreach ($users as $user) {
                $eloquentUser = User::find($user->id); // Load the user as an Eloquent model
                if ($eloquentUser) {
                    if ($user->role == 'admin_all')  {
                        $eloquentUser->addRole($adminRole);
                    } elseif ($user->role == 'admin_staff') {
                        $eloquentUser->addRole($adminRole);
                    } elseif ($user->role == 'admin_view') {
                        $eloquentUser->addRole($adminRole);
                    } elseif ($user->role == 'institution_all') {
                        $eloquentUser->addRole($institutionRole);
                    } elseif ($user->role == 'institution_all_view') {
                        $eloquentUser->addRole($institutionRole);
                    } elseif ($user->role == 'accounts_all') {
                        $eloquentUser->addRole($accountantRole);
                    }
        
                    $eloquentUser->save();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
