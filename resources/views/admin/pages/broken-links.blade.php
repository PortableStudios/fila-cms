<x-filament-panels::page>
    <p><b>Last Scan:</b> {{ $this->getLastScan() }}</p>
    <p class="mb-2"><b>Status:</b> {{ $this->lastScanStatus() }}</p>
    {{ $this->table }}
</x-filament-panels::page>
