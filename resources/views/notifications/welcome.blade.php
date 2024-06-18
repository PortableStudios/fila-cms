<x-mail::message>

# Welcome {{ $user?->name ?? 'User' }}

You're now ready to explore the platform.

Use the link below to add details to your profile.

<x-mail::button :url="route('user-profile-information.show')">
    View profile
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
