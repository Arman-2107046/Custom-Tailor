<?php

namespace App\Filament\Pages;

use App\Services\BulkUploadService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;


class BulkUpload extends Page
{
    protected string $view = 'filament.pages.unified-bulk-upload';


    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cloud-arrow-up';


    protected static ?string $navigationLabel = 'Bulk Upload';


    protected static string|\UnitEnum|null $navigationGroup = 'Management';



    public function processUploads(array $files): void
    {
        $service = app(BulkUploadService::class);


        $result = $service->process($files);



        Notification::make()
            ->title('Bulk Upload Completed')
            ->body(
                $result['message'] ?? 'Upload completed'
            )
            ->success()
            ->send();
    }



    public function getCloudinaryCloudName(): string
    {
        $cloud = env('CLOUDINARY_CLOUD_NAME', '');



        if (! empty($cloud)) {
            return $cloud;
        }



        $url = env('CLOUDINARY_URL');



        if ($url) {

            return parse_url(
                $url,
                PHP_URL_HOST
            ) ?? '';

        }



        return '';
    }



    public function getCloudinaryUploadPreset(): string
    {
        return env(
            'CLOUDINARY_UPLOAD_PRESET',
            ''
        );
    }
}
