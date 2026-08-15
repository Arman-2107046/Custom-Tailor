<?php

namespace App\Filament\Resources\Sleeves\Pages;

use App\Filament\Resources\Sleeves\SleeveResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSleeve extends EditRecord
{
    protected static string $resource = SleeveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
