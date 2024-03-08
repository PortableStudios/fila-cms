<?php

namespace Portable\FilaCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class MakeUser extends Command
{
    protected $signature = 'fila-cms:make-user {--dry-run}';

    protected $description = 'Creates a new user with an admin role';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // check if Role table exists
        if (Schema::hasTable('role') === false) {
        }

        // check first if there's an admin role in place
        $checkRole = Role::where('name', 'Admin')->first();

        if (is_null($checkRole) === true) { // no role, prompt the user to install
            $this->error('Cannot locate Admin role, have you run php artisan fila-cms:install?');

            return false;
        }

        $userModelClass = config('auth.providers.users.model');
        $userModel = new $userModelClass();
        $userFieldsRaw = Schema::getColumnListing($userModel->getTable());

        $excludeFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'email_verified_at'];
        $userFields = array_diff($userFieldsRaw, $excludeFields);

        foreach ($userFields as $key => $field) {
            $userModel->{$field} = $this->ask('Enter admin '.$field);
        }

        // auto-populate other fields
        if (in_array('email_verified_at', $userFieldsRaw)) {
            $userModel->email_verified_at = now();
        }

        // assign role to user
        $userModel->assignRole($checkRole);

        if ($dryRun) {
            $this->info('User to be created');
            $this->info($userModel);
        } else {
            $this->info('User created');
            $userModel->save();
        }
    }
}
