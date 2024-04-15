<?php

namespace Portable\FilaCms\Commands;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Spatie\Permission\Traits\HasRoles;
use Portable\FilaCms\Contracts\HasLogin;

class AddUserConcerns extends Command
{
    protected $signature = 'fila-cms:add-user-concerns {--dry-run}';

    protected $description = 'Add the traits and interfaces to the User model required by FilaCms';

    public function handle()
    {
        $userModel = config('auth.providers.users.model');

        $dryRun = $this->option('dry-run');

        $this->info("Adding traits and interfaces to User Model");

        $traits = [
            HasRoles::class,
            HasLogin::class,
        ];

        $interfaces = [
            FilamentUser::class,
        ];

        $userContents = File::get((new ReflectionClass($userModel))->getFileName());

        $existingTraits = class_uses_recursive($userModel);

        foreach ($traits as $trait) {
            if (in_array($trait, $existingTraits)) {
                $this->info("User already has trait $trait, skipping");
                continue;
            }

            if (strpos($userContents, $trait) === false) {
                $this->info("Adding trait $trait");

                $part1End = strpos($userContents, '{', strpos($userContents, 'class'));
                $part1 = substr($userContents, 0, $part1End + 1);
                $part2 = substr($userContents, $part1End + 1);

                $userContents = $part1 . "\n    use \\" . $trait . ';' . $part2;
            }
        }

        foreach ($interfaces as $interface) {
            if (strpos($userContents, $interface) === false) {
                $this->info("Adding interface $interface");
                $hasImplements = false;

                if (strpos($userContents, 'implements') !== false) {
                    $part1End = strpos($userContents, '{', strpos($userContents, 'implements '));
                    $hasImplements = true;
                } else {
                    $part1End = strpos($userContents, '{', strpos($userContents, 'extends '));
                }
                $part1 = substr($userContents, 0, $part1End - 1);
                $part2 = "\n" .  substr($userContents, $part1End);

                if ($hasImplements) {
                    $userContents = $part1 . ', \\' . $interface . $part2;
                } else {
                    $userContents = $part1 . ' implements \\' . $interface . $part2;
                }
            } else {
                $this->info("User already has interface $interface, skipping");
            }
        }

        if (!strpos($userContents, 'canAccessFilament')) {
            $part1End = strrpos($userContents, '}');
            $part1 = substr($userContents, 0, $part1End);
            $part2 = substr($userContents, $part1End);

            $userContents = $part1 . "\n\n    public function canAccessFilament(): bool\n    {\n        // This is required on Front and Back end.  Add more specific controls with authenticate middleware.\n        return true;\n    }\n\n" . $part2;
        }

        if (!strpos($userContents, 'canAccessPanel')) {
            $part1End = strrpos($userContents, '}');
            $part1 = substr($userContents, 0, $part1End);
            $part2 = substr($userContents, $part1End);

            $userContents = $part1 . "\n\n    public function canAccessPanel(\$panel): bool\n    {\n        // This is required on Front and Back end.  Add more specific controls with authenticate middleware.\n        return true;\n    }\n\n" . $part2;
        }

        if ($dryRun) {
            $this->info("Dry run, not writing to file.  Output:");
            $this->info($userContents);
        } else {
            File::put((new ReflectionClass($userModel))->getFileName(), $userContents);
        }
    }
}
