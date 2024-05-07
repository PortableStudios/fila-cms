<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  @include('fila-cms::layouts.document-head')
</head>

<body>
  <main class="min-h-screen flex flex-col justify-between">
    <section class="flex-grow">
      @include('fila-cms::layouts.page-head')
      <div class="main-content">
        {{ $slot }}
      </div>
    </section>
    <footer>
      @include('fila-cms::layouts.page-footer')
    </footer>
  </main>
</body>

</html>
