<?php

namespace Portable\FilaCms\Actions\Fortify;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    public function reset(Model $user, array $input): void
    {
        Validator::make(
            $input,
            [
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    Password::min(16)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised()
            ],
        ]
        )->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
