<form method="post" action="{{ route('login') }}">
  @csrf
  <div class="w-1/2 w-full form-group">
    <label class="inline-block w-1/2" for="email">Email</label>
    <input class="inline-block w-1/2" type="email" name="email" id="email" class="form-control" required>
  </div>
  <div class="w-1/2 w-full form-group">
    <label class="inline-block w-1/2" for="password">Password</label>
    <input class="inline-block w-1/2" type="password" name="password" id="password" class="form-control" required>
    <div class="inline-block w-1/2 pt-2 text-right">
        <a href="{{ route('password.request') }}" class="text-slate-500">forgot password</a>
    </div>
  </div>

  <button type="submit" class="btn btn-primary">Login</button>
  <x-fila-cms::auth.sso-links />
</form>
