<form method="POST" action="{{ route('user-profile-information.update') }}" enctype="multipart/form-data">
  @csrf
  @method('PUT')

  <!-- Name -->
  <div>
    <x-fila-cms::input-label for="email" :value="__('Name')" />
    <x-fila-cms::text-input id="email" class="block mt-1 w-full" name="name" :value="old('name', auth()->user()->name)" required autofocus
      autocomplete="username" />

    @error('name', 'updateProfileInformation')
      <x-fila-cms::input-error :messages="$message" class="mt-2" />
    @enderror
  </div>
  <div>
    <x-fila-cms::input-label for="email" :value="__('Email')" />
    <x-fila-cms::text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', auth()->user()->email)"
      required autofocus autocomplete="username" />

    @error('email', 'updateProfileInformation')
      <x-fila-cms::input-error :messages="$message" class="mt-2" />
    @enderror
  </div>
  <!-- Password -->
  <div class="mt-4">
    <x-fila-cms::input-label for="password" :value="__('Password')" />
    <x-fila-cms::text-input id="password" class="block mt-1 w-full" type="password" name="password"
      autocomplete="new-password" />
    @error('password', 'updateProfileInformation')
      <x-fila-cms::input-error :messages="$message" class="mt-2" />
    @enderror
  </div>

  <!-- Confirm Password -->
  <div class="mt-4">
    <x-fila-cms::input-label for="password_confirmation" :value="__('Confirm Password')" />

    <x-fila-cms::text-input id="password_confirmation" class="block mt-1 w-full" type="password"
      name="password_confirmation" autocomplete="new-password" />

    @error('password_confirmation', 'updateProfileInformation')
      <x-fila-cms::input-error :messages="$message" class="mt-2" />
    @enderror
  </div>

  <button type="submit" class="btn btn-primary">Update Profile</button>
</form>
