<?php

namespace App\Filament\Resources\Fabrics\Pages;

use App\Filament\Imports\FabricImporter;
use App\Filament\Resources\Fabrics\FabricResource;
use App\Models\Fabric;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListFabrics extends ListRecords
{
    protected static string $resource = FabricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            ImportAction::make()
                ->importer(FabricImporter::class)
                ->label('Import Fabrics'),

            Action::make('bulkUploadFabrics')
                ->label('Bulk Upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalWidth('lg')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn () => view('filament.modals.bulk-upload-images', [
                    'cloudName' => $this->getCloudinaryCloudName(),
                    'uploadPreset' => env('CLOUDINARY_UPLOAD_PRESET', ''),
                    'title' => 'Upload Fabric Images',
                    'subtitle' => 'Drag & drop fabric images here, or click to browse',
                    'filenameHint' => 'Name__Price__Default__Status.png',
                    'wireMethod' => 'processUploads',
                ])),
        ];
    }

    /**
     * Filename format:
     *
     * Name__Price__Default__Status.png
     *
     * Examples:
     *
     * Black Wool__120__1__1.png
     * Navy Blue__150__0__1.png
     * Grey Stripe__180__0__1.png
     *
     * Default:
     * 1 = default
     * 0 = not default
     *
     * Status:
     * 1 = active
     * 0 = inactive
     */
    public function processUploads(array $files): void
    {
        $imported = 0;
        $failed = [];
        $hasNewDefault = false;
        $importedNames = [];

        foreach ($files as $file) {
            $originalName = $file['name'] ?? null;
            $url = $file['url'] ?? null;

            if (! $originalName || ! $url) {
                continue;
            }

            $filename = pathinfo($originalName, PATHINFO_FILENAME);
            $parts = explode('__', $filename);

            if (count($parts) !== 4) {
                $failed[] = $originalName . ' — invalid format';
                continue;
            }

            [$name, $price, $isDefault, $status] = $parts;

            if (! is_numeric($price)) {
                $failed[] = $originalName . ' — invalid price';
                continue;
            }

            $name = trim($name);
            $isDefault = (bool) (int) $isDefault;

            if ($isDefault) {
                $hasNewDefault = true;
            }

            Fabric::updateOrCreate(
                ['name' => $name],
                [
                    'price' => (float) $price,
                    'image' => $url,
                    'is_default' => $isDefault,
                    'status' => (bool) (int) $status,
                ]
            );

            $importedNames[] = $name;
            $imported++;
        }

        if ($hasNewDefault) {
            Fabric::where('is_default', true)
                ->whereNotIn('name', $importedNames)
                ->update([
                    'is_default' => false,
                ]);
        }

        Notification::make()
            ->title('Fabric import completed')
            ->body(
                "{$imported} fabric(s) imported."
                . (
                    count($failed)
                        ? ' Failed: ' . implode(', ', $failed)
                        : ''
                )
            )
            ->success(empty($failed))
            ->warning(! empty($failed))
            ->send();
    }

    private function getCloudinaryCloudName(): string
    {
        $cloud = env('CLOUDINARY_CLOUD_NAME', '');

        if (empty($cloud)) {
            $cloudinaryUrl = env('CLOUDINARY_URL');

            if ($cloudinaryUrl) {
                $cloud = parse_url($cloudinaryUrl, PHP_URL_HOST) ?? '';
            }
        }

        return $cloud;
    }
}
