<x-fila-cms::layouts.app>
  @if (session('error'))
    <div class="mb-4 text-sm font-medium text-red-600">
      {{ session('error') }}
    </div>
  @endif
  <x-fila-cms::auth.login-form />
</x-fila-cms::layouts.app>
