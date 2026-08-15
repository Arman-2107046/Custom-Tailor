<?php

namespace App\Filament\Resources\SidePocketTypes\Pages;

use App\Filament\Resources\SidePocketTypes\SidePocketTypeResource;
use App\Models\SidePocketType;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSidePocketTypes extends ListRecords
{
    protected static string $resource = SidePocketTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('import')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Import Side Pocket Types')
                ->modalWidth('2xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn () => view(
                    'filament.modals.bulk-upload-images',
                    [
                        'cloudName' => $this->getCloudinaryCloudName(),
                        'uploadPreset' => env('CLOUDINARY_UPLOAD_PRESET', ''),
                        'title' => 'Upload Side Pocket Type Images',
                        'subtitle' => 'Drag & drop Side Pocket Type images here, or click to browse',
                        'filenameHint' => 'Single_Welt.png',
                        'wireMethod' => 'processUploads',
                        'accept' => 'image/*',
                    ]
                )),
        ];
    }

    public function processUploads(array $files): void
    {
        foreach ($files as $file) {

            if (
                ! isset($file['name']) ||
                ! isset($file['url'])
            ) {
                continue;
            }

            $filename = pathinfo(
                $file['name'],
                PATHINFO_FILENAME
            );

            /*
             * Example:
             *
             * Single_Welt.png
             *
             * $filename:
             * Single_Welt
             */

            $parts = explode('_', $filename);

            /*
             * Must have at least:
             *
             * Name_Code
             */

            if (count($parts) < 2) {
                continue;
            }

            /*
             * Last part = code
             */

            $code = array_pop($parts);

            /*
             * Everything before the last underscore = name
             */

            $name = implode('_', $parts);

            /*
             * Convert underscores in the name to spaces.
             *
             * Example:
             *
             * Flap_Single_Button
             *
             * becomes:
             *
             * Flap Single
             */

            $name = str_replace('_', ' ', $name);

            /*
             * Clean values.
             */

            $name = trim($name);
            $code = trim($code);

            /*
             * Ignore invalid filenames.
             */

            if (
                $name === '' ||
                $code === ''
            ) {
                continue;
            }

            /*
             * Create or update record.
             *
             * Code is used as the unique identifier.
             */

            SidePocketType::updateOrCreate(
                [
                    'code' => $code,
                ],
                [
                    'name' => $name,
                    'diagram' => $file['url'],
                ]
            );
        }

        Notification::make()
            ->title('Side Pocket Types imported successfully')
            ->success()
            ->send();

        $this->resetTable();
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
