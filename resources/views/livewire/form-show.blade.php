<div>

  @if ($submitted)
    <h1>{{ $record->confirmation_title }}</h1>

    {!! tiptap_converter()->asHtml($record->confirmation_text) !!}
  @else
    <h1>{{ $record->title }}</h1>
    <form wire:submit="submitForm">
      {{ $this->form }}

      <br />

      <x-fila-cms::primary-button>
        Submit
      </x-fila-cms::primary-button>
    </form>
  @endif
  <x-filament-actions::modals />
</div>
