<?php

namespace Portable\FilaCms\Actions\Fortify;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(Authenticatable $user, array $input): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ];

        $fields = [
            'name' => $input['name'],
            'email' => $input['email'],
        ];

        if (isset($input['password'])) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            $fields['password'] = Hash::make($input['password']);
        }

        Validator::make($input, $rules)->validateWithBag('updateProfileInformation');

        if (
            $input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail
        ) {
            $this->updateVerifiedUser($user, $fields);
        } else {
            $user->forceFill($fields)->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(MustVerifyEmail $user, array $fields): void
    {
        $fields['email_verified_at'] = null;
        $user->forceFill($fields)->save();

        $user->sendEmailVerificationNotification();
    }
}
