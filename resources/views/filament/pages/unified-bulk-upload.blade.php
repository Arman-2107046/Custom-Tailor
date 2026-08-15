<x-filament-panels::page>

    <x-filament::section>

        <x-slot name="heading">
            Unified Bulk Upload
        </x-slot>


        <p class="text-sm text-gray-500">
            Upload all customization images here.
            Filename prefix decides destination.
        </p>


    </x-filament::section>



    <x-filament::section>


        @include('filament.modals.bulk-upload-images', [
        'cloudName' => $this->getCloudinaryCloudName(),
        'uploadPreset' => $this->getCloudinaryUploadPreset(),

        'title' => 'Upload All Images',

        'subtitle' => 'Drag & drop images here, or click to browse',

        'filenameHint' => 'BD__SB1_Black.png',

        'wireMethod' => 'processUploads',

        'accept' => 'image/*',

        ])


    </x-filament::section>


</x-filament-panels::page>
