<div>
  <input type="hidden" wire:model="{{ $getStatePath() }}" class="media-library-input" />

  <livewire:media-library-table />

  @script
    <script>
      Livewire.on('media-file-selected', (event) => {
        $wire.set('{{ $getStatePath() }}', event[0].id);
      });
    </script>
  @endscript
</div>
