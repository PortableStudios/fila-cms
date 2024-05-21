<x-fila-cms::layouts.app>
    <div class="w-full md:w-1/2">
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div>
                <x-fila-cms::input-label for="email" :value="__('Email')" />
                <x-fila-cms::text-input id="email" class="block w-full mt-1" type="email" name="email"
                    :value="old('email', request()->email)" required autofocus autocomplete="username" />

                <x-fila-cms::input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
            <x-fila-cms::primary-button>
                {{ __('Reset Password') }}
            </x-fila-cms::primary-button>
            </div>
        </form>
    </div>

</x-fila-cms::layouts.app>
