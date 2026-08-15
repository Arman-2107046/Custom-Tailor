<?php

namespace App\Filament\Resources\SidePockets\Pages;

use App\Filament\Resources\SidePockets\SidePocketResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSidePocket extends EditRecord
{
    protected static string $resource = SidePocketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
