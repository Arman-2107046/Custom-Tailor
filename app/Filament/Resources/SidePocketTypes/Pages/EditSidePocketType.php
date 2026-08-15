<?php

namespace App\Filament\Resources\SidePocketTypes\Pages;

use App\Filament\Resources\SidePocketTypes\SidePocketTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSidePocketType extends EditRecord
{
    protected static string $resource = SidePocketTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
