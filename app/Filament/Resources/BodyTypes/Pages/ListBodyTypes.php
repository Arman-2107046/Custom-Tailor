<?php

namespace App\Filament\Resources\BodyTypes\Pages;

use App\Filament\Resources\BodyTypes\BodyTypeResource;
use App\Models\BodyType;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBodyTypes extends ListRecords
{
    protected static string $resource = BodyTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('bulkUploadBodyTypes')
                ->label('Bulk Upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalWidth('lg')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn () => view('filament.modals.bulk-upload-images', [
                    'cloudName' => $this->getCloudinaryCloudName(),
                    'uploadPreset' => env('CLOUDINARY_UPLOAD_PRESET', ''),
                    'title' => 'Upload Body Type Images',
                    'subtitle' => 'Drag & drop images here, or click to browse',
                    'filenameHint' => 'Name_Code.png',
                    'wireMethod' => 'processUploads',
                ])),
        ];
    }

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

            $filename = trim(pathinfo($originalName, PATHINFO_FILENAME));

            /*
             * Expected filename:
             * Double-breasted 2 buttons_DB2.png
             */

            $parts = explode('_', $filename);

            if (count($parts) !== 2) {
                $failed[] = $originalName . ' — filename must be Name_CODE.png';
                continue;
            }

            [$name, $code] = $parts;

            $name = trim($name);
            $code = strtoupper(trim($code));

            BodyType::updateOrCreate(
                [
                    'code' => $code,
                ],
                [
                    'name' => $name,
                    'code' => $code,
                    'diagram' => $url,
                ]
            );

            $imported++;
        }

        Notification::make()
            ->title('Body Types imported')
            ->body(
                "{$imported} body type(s) imported."
                . (
                    count($failed)
                        ? ' Failed: ' . implode(', ', $failed)
                        : ''
                )
            )
            ->success(count($failed) === 0)
            ->warning(count($failed) > 0)
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
