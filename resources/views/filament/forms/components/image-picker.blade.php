<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
  <div x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }} }">
    <input type="hidden" x-model="state" class="media-library-input" id="{{ $getStatePath() }}" />

    <div class="media-library-preview border rounded shadow m-2 relative"
      style="max-width: 120px; height: 120px; width: 120px; background-repeat:no-repeat; background-image: url('{{ $currentImage() }}'); background-size: contain; background-position: center;">
      @if ($hasImage())
        <button type="button" class="absolute m-1 bg-white text-danger border rounded-full block font-bold"
          style="top: -10px; right: -10px; color: red; width: 20px;" x-on:click="state = ''">x</button>
      @endif
      <div x-on:click="$dispatch('open-modal', {id: '{{ $getStatePath() }}-image-picker'})"
        class="w-full h-full bg-danger">
      </div>
    </div>

    <x-filament::modal id="{{ $getStatePath() }}-image-picker" width="5xl">
      <x-slot name="header">
        Select Image
      </x-slot>
      <div>
        <livewire:media-library-table jsKey="{{ $getStatePath() }}-media-picker" />
        <input type="hidden" id="{{ $getStatePath() }}-selected" />

      </div>
      <x-slot name="footer">
        <x-filament::button
          x-on:click="$dispatch('close-modal', {id: '{{ $getStatePath() }}-image-picker'}); state = document.getElementById('{{ $getStatePath() }}-selected').value;">
          OK</x-filament::button>
        <x-filament::button class="white"
          x-on:click="$dispatch('close-modal', {id: '{{ $getStatePath() }}-image-picker'})">Cancel</x-filament::button>
      </x-slot>

    </x-filament::modal>
    @script
      <script>
        Livewire.on('media-file-selected', (event) => {
          if (event[0].jsKey === '{{ $getStatePath() }}-media-picker') {
            document.getElementById('{{ $getStatePath() }}-selected').value = event[0].id;
          }
        });
      </script>
    @endscript
    <!-- Field -->
  </div>
</x-dynamic-component>
