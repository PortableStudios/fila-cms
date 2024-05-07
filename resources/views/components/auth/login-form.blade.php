<form method="post" action="{{ route('login') }}">
  @csrf
  <div class="form-group w-1/2 w-full">
    <label class="inline-block w-1/2" for="email">Email</label>
    <input class="inline-block w-1/2" type="email" name="email" id="email" class="form-control" required>
  </div>
  <div class="form-group w-1/2 w-full">
    <label class="inline-block w-1/2" for="password">Password</label>
    <input class="inline-block w-1/2" type="password" name="password" id="password" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-primary">Login</button>
</form>
