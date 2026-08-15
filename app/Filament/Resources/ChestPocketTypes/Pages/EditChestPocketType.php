<?php

namespace App\Filament\Resources\ChestPocketTypes\Pages;

use App\Filament\Resources\ChestPocketTypes\ChestPocketTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChestPocketType extends EditRecord
{
    protected static string $resource = ChestPocketTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
