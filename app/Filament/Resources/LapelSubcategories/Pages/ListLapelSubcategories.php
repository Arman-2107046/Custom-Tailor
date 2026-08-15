<?php

namespace App\Filament\Resources\LapelSubcategories\Pages;

use App\Filament\Imports\LapelSubCategoryImporter;
use App\Filament\Resources\LapelSubcategories\LapelSubcategoryResource;
use App\Models\LapelSubCategory;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLapelSubcategories extends ListRecords
{
    protected static string $resource = LapelSubcategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            ImportAction::make()
                ->importer(LapelSubCategoryImporter::class)
                ->label('Import Lapel Subcategories'),

            Action::make('bulkUploadLapelSubcategories')
                ->label('Bulk Upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalWidth('lg')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn () => view(
                    'filament.modals.bulk-upload-images',
                    [
                        'cloudName' => $this->getCloudinaryCloudName(),
                        'uploadPreset' => env('CLOUDINARY_UPLOAD_PRESET', ''),
                        'title' => 'Upload Lapel Subcategory Images',
                        'subtitle' => 'Drag & drop lapel subcategory images here, or click to browse',
                        'filenameHint' => 'Slim.png',
                        'wireMethod' => 'processUploads',
                    ]
                )),
        ];
    }

    /**
     * Filename format:
     *
     * Slim.png
     * Standard.png
     * Wide.png
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

            $name = trim(
                pathinfo($originalName, PATHINFO_FILENAME)
            );

            if ($name === '') {
                $failed[] = $originalName . ' — invalid filename';
                continue;
            }

            LapelSubCategory::updateOrCreate(
                [
                    'name' => $name,
                ],
                [
                    'diagram' => $url,
                    'status' => true,
                ]
            );

            $imported++;
        }

        Notification::make()
            ->title('Lapel subcategories imported')
            ->body(
                "{$imported} subcategor"
                . ($imported === 1 ? 'y' : 'ies')
                . " imported."
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
                $cloud = parse_url(
                    $cloudinaryUrl,
                    PHP_URL_HOST
                ) ?? '';
            }
        }

        return $cloud;
    }
}
