    <x-fila-cms::layouts.app>

      <div class="w-full md:w-1/2">
        <form method="POST" action="{{ route('password.update') }}">
          @csrf

          <!-- Password Reset Token -->
          <input type="hidden" name="token" value="{{ request()->route('token') }}">

          <!-- Email Address -->
          <div>
            <x-fila-cms::input-label for="email" :value="__('Email')" />
            <x-fila-cms::text-input id="email" class="block mt-1 w-full" type="email" name="email"
              :value="old('email', request()->email)" required autofocus autocomplete="username" />

            <x-fila-cms::input-error :messages="$errors-> get('email')" class="mt-2" />
          </div>

          <!-- Password -->
          <div class="mt-4">
            <x-fila-cms::input-label for="password" :value="__('Password')" />
            <x-fila-cms::text-input id="password" class="block mt-1 w-full" type="password" name="password" required
              autocomplete="new-password" />
            <x-fila-cms::input-error :messages="$errors-> get('password')" class="mt-2" />
          </div>

          <!-- Confirm Password -->
          <div class="mt-4">
            <x-fila-cms::input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-fila-cms::text-input id="password_confirmation" class="block mt-1 w-full" type="password"
              name="password_confirmation" required autocomplete="new-password" />

            <x-fila-cms::input-error :messages="$errors-> get('password_confirmation')" class="mt-2" />
          </div>

          <div class="flex items-center justify-end mt-4">
            <x-fila-cms::primary-button>
              {{ __('Reset Password') }}
            </x-fila-cms::primary-button>
          </div>
        </form>
      </div>

    </x-fila-cms::layouts.app>
