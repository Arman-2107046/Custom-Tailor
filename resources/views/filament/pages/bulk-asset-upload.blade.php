<x-filament-panels::page>

    {{ $this->form }}

    <div class="mt-6">
        <x-filament::button
            wire:click="import"
            icon="heroicon-o-arrow-up-tray"
        >
            Import Assets
        </x-filament::button>
    </div>

</x-filament-panels::page>
