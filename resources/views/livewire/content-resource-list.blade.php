<div>
  <h1>{{ $title }}</h1>

  {{ $message }}

  <!-- Two columns -->
  <div class="flex mb-4">
    <div class="filters-column">
      <form wire:submit.prevent="submit">
        {{ $this->form }}
        <div>&nbsp;</div>
        <button type="submit" class="btn-primary">
          Submit
        </button>
      </form>
    </div>
    <div class="listing-column">
      @forelse ($results as $result)
        <x-fila-cms::content-listing-card :title="$result->title" :excerpt="$result->excerpt" :image="$result->image" :url="route($showRoute, $result)" />
      @empty
        <p>No results found</p>
      @endforelse

      {{ $results->links() }}
    </div>
  </div>
</div>
