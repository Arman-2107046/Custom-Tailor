<?php

namespace App\Filament\Resources\ChestPockets\Pages;

use App\Filament\Resources\ChestPockets\ChestPocketResource;
use App\Services\BulkUpload\ChestPocketsUploader;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListChestPockets extends ListRecords
{
    protected static string $resource = ChestPocketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('bulkUpload')
                ->label('Bulk Upload')
                ->icon('heroicon-o-cloud-arrow-up')
                ->action(function (array $data) {

                    $uploader = app(ChestPocketsUploader::class);

                    foreach ($data['files'] ?? [] as $file) {

                        $result = $uploader->handle($file);

                        if (! $result['success']) {

                            Notification::make()
                                ->title('Bulk Upload Failed')
                                ->body($result['message'])
                                ->danger()
                                ->send();

                            return;
                        }
                    }

                    Notification::make()
                        ->title('Bulk Upload Successful')
                        ->body('All chest pockets were imported successfully.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
