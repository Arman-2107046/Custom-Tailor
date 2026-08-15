<?php

namespace App\Filament\Resources\ButtonImages\Pages;

use App\Filament\Imports\ButtonImageImporter;
use App\Filament\Resources\ButtonImages\ButtonImageResource;
use App\Models\ButtonImage;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListButtonImages extends ListRecords
{
    protected static string $resource = ButtonImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            ImportAction::make()
                ->importer(ButtonImageImporter::class)
                ->label('Import Button Images'),

            Action::make('bulkUploadButtonImages')
                ->label('Bulk Upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalWidth('lg')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn () => view('filament.modals.bulk-upload-images', [
                    'cloudName' => $this->getCloudinaryCloudName(),
                    'uploadPreset' => env('CLOUDINARY_UPLOAD_PRESET', ''),
                    'title' => 'Upload Button Images',
                    'subtitle' => 'Drag & drop button images here, or click to browse',
                    'filenameHint' => 'Brown Horn.png',
                    'wireMethod' => 'processUploads',
                ])),
        ];
    }

    /**
     * Filename format:
     *
     * Brown Horn.png
     * Black Horn.png
     * Mother of Pearl.png
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

            ButtonImage::updateOrCreate(
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
            ->title('Button image import completed')
            ->body(
                "{$imported} button image(s) imported."
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
