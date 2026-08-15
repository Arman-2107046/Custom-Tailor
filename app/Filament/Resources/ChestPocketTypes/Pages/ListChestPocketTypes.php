<?php

namespace App\Filament\Resources\ChestPocketTypes\Pages;

use App\Filament\Resources\ChestPocketTypes\ChestPocketTypeResource;
use App\Models\ChestPocketType;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListChestPocketTypes extends ListRecords
{
    protected static string $resource = ChestPocketTypeResource::class;


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('import')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Import Chest Pocket Types')
                ->modalWidth('2xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn () => view(
                    'filament.modals.bulk-upload-images',
                    [
                        'cloudName' => $this->getCloudinaryCloudName(),
                        'uploadPreset' => env('CLOUDINARY_UPLOAD_PRESET', ''),
                        'title' => 'Upload Chest Pocket Type Images',
                        'subtitle' => 'Drag & drop Chest Pocket Type images here, or click to browse',
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
             * filename:
             * Single_Welt
             */


            $parts = explode('_', $filename);


            /*
             * Must have:
             *
             * Name_Code
             */


            if (count($parts) < 2) {
                continue;
            }


            /*
             * Last part is code
             */

            $code = array_pop($parts);


            /*
             * Remaining parts are name
             */

            $name = implode('_', $parts);


            /*
             * Replace underscore with spaces
             */

            $name = str_replace('_', ' ', $name);


            $name = trim($name);
            $code = trim($code);


            if (
                $name === '' ||
                $code === ''
            ) {
                continue;
            }


            ChestPocketType::updateOrCreate(
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
            ->title('Chest Pocket Types imported successfully')
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
