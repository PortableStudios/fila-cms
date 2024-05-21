<x-filament-panels::page>
    @if (auth()->user()->hasEnabledTwoFactorAuthentication())
        <div class="mb-4">
            <p class="font-bold">Your two-factor authentication is active.</p>
            <p>Your recovery codes:</p>
        </div>

        <div class="p-2">
            @foreach(auth()->user()->recoveryCodes() as $code)
            <div class="py-2">
                <p>{{ $code }}</p>
            </div>
            @endforeach
        </div>
        <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
            @csrf
            @method('DELETE')
            <x-filament::button color="danger" type="submit">Disable 2FA</x-filament::button>
        </form>
    @else
        @if (session('status') == 'two-factor-authentication-enabled')
            <div class="mb-2 font-bold">
                Please finish configuring two factor authentication below.
            </div>
            {!! auth()->user()->twoFactorQrCodeSvg() !!}
            <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}">
                @csrf
                <x-filament::input.wrapper class="mb-2">
                    <x-filament::input type="text" name="code"/>
                </x-filament::input.wrapper>

                <x-filament::button color="info" type="submit">Verify</x-filament::button>
            </form>
        @else
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                <p class="mb-2 font-bold">Two factor authentication is not enabled.</p>
                <x-filament::button type="submit">Enable</x-filament::button>
            </form>
        @endif
    @endif

  <x-filament-actions::modals />

</x-filament-panels::page>
