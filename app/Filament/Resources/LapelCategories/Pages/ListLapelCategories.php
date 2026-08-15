<?php

namespace App\Filament\Resources\LapelCategories\Pages;

use App\Filament\Imports\LapelCategoryImporter;
use App\Filament\Resources\LapelCategories\LapelCategoryResource;
use App\Models\LapelCategory;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLapelCategories extends ListRecords
{
    protected static string $resource = LapelCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            ImportAction::make()
                ->importer(LapelCategoryImporter::class)
                ->label('Import Lapel Categories'),

            Action::make('bulkUploadLapelCategories')
                ->label('Bulk Upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalWidth('lg')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn () => view('filament.modals.bulk-upload-images', [
                    'cloudName' => $this->getCloudinaryCloudName(),
                    'uploadPreset' => env('CLOUDINARY_UPLOAD_PRESET', ''),
                    'title' => 'Upload Lapel Category Images',
                    'subtitle' => 'Drag & drop lapel category images here, or click to browse',
                    'filenameHint' => 'Classic.png',
                    'wireMethod' => 'processUploads',
                ])),
        ];
    }

    /**
     * Filename format:
     *
     * Classic.png
     * Peak.png
     * Notch.png
     * Shawl.png
     */
    public function processUploads(array $files): void
    {
        $imported = 0;
        $failed = [];

        foreach ($files as $file) {

            $originalName = $file['name'] ?? null;
            $url = $file['url'] ?? null;

            if (! $originalName || ! $url) {
                continue;
            }

            $name = trim(pathinfo($originalName, PATHINFO_FILENAME));

            if ($name === '') {
                $failed[] = $originalName . ' — invalid filename';
                continue;
            }

            LapelCategory::updateOrCreate(
                [
                    'name' => $name,
                ],
                [
                    'diagram' => $url,
                ]
            );

            $imported++;
        }

        Notification::make()
            ->title('Lapel categories imported')
            ->body(
                "{$imported} lapel categor" . ($imported === 1 ? 'y' : 'ies') . " imported."
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
