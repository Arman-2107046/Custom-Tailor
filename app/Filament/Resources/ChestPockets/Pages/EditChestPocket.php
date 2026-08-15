<?php

namespace App\Filament\Resources\ChestPockets\Pages;

use App\Filament\Resources\ChestPockets\ChestPocketResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChestPocket extends EditRecord
{
    protected static string $resource = ChestPocketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
