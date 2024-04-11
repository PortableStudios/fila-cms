<x-dynamic-component class="inline-block" :component="$getFieldWrapperView()" :field="$field">
  <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">

  </div>
</x-dynamic-component>
