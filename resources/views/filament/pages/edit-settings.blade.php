<x-filament-panels::page>
  <form wire:submit="save">
    {{ $this->form }}

    <div class="mt-4">&nbsp;</div>
  </form>


  <div style="max-width: 100px;">
    <x-filament::button wire:click="save">
      Save
    </x-filament::button>
  </div>
</x-filament-panels::page>
