<x-filament-panels::page>
    <form method="POST" action="{{ url('/two-factor-challenge') }}">
        @csrf
        <x-filament::input.wrapper class="mb-2">
            <x-filament::input type="text" name="code" placeholder="2FA Code"/>
        </x-filament::input.wrapper>

        <x-fila-cms::hr/>

        <x-filament::input.wrapper class="mb-2">
            <x-filament::input type="text" name="recovery" placeholder="Recovery Code"/>
        </x-filament::input.wrapper>

        <x-filament::button color="info" type="submit">Authenticate</x-filament::button>
    </form>
</x-filament-panels::page>