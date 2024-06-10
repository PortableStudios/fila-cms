<x-filament-panels::page>
  <form wire:submit="save">
    {{ $this->form }}
  </form>
  <div style="max-width: 100px;">
    <x-filament::button wire:click="save">
      Save
    </x-filament::button>
  </div>
  <x-filament-actions::modals />

</x-filament-panels::page>
