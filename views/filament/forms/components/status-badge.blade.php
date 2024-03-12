<x-dynamic-component class="inline-block" :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        <!-- Interact with the `state` property in Alpine.js -->
        <x-filament::badge :color="$getColor()" :text="$getState()">
            {{ $getState() }}
        </x-filament::badge>
    </div>
</x-dynamic-component>
