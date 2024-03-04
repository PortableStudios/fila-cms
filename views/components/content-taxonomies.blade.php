  <div class="taxonomies">
    @foreach ($record->terms->groupBy('taxonomy.name') as $taxonomy => $terms)
      <div>
        <strong>{{ Str::plural($taxonomy) }}: </strong>
        @foreach ($terms as $term)
          <a href="#" class="taxonomy-term">{{ $term->name }}</a>
        @endforeach
      </div>
    @endforeach
  </div>
