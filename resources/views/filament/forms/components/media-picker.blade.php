<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
  <div class="media-library-preview border rounded shadow m-2 relative"
    style="max-width: 120px; height: 120px; width: 120px; background-repeat:no-repeat; background-image: url('{{ $currentMedia() }}'); background-size: contain; background-position: center;">
    @if ($hasMedia())
      <button type="button" class="absolute m-1 bg-white text-danger border rounded-full block font-bold"
        style="top: -10px; right: -10px; color: red; width: 20px;"
        wire:click="{{ $getAction('clear-media')->getLivewireClickHandler() }}">x</button>
    @endif
    <div wire:click="{{ $getAction('pick-media')->getLivewireClickHandler() }}" class="w-full h-full bg-danger">
    </div>
  </div>

</x-dynamic-component>
