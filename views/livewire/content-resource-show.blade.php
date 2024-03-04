<div>
  <h1>{{ $record->title }}</h1>
  <div class="authors">
    Written by {{ $record->displayAuthor }}, {{ $record->publish_at->format('F j, Y') }}
  </div>

  {{ $message }}

  {!! $record->contents !!}

  <x-fila-cms::content-taxonomies :record="$record" />
</div>
