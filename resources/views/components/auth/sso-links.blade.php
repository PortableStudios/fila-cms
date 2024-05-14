@foreach (config('fila-cms.sso.providers', ['google', 'facebook', 'linkedin']) as $provider)
  @if (config('settings.sso.' . $provider . '.client_id') && config('settings.sso.' . $provider . '.client_secret'))
    <a href="{{ route('login.' . $provider) }}" class="btn btn-primary">{{ ucfirst($provider) }} Login</a><br />
  @endif
@endforeach
