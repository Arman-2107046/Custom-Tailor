<?php

namespace App\Filament\Resources\SleeveTypes\Pages;

use App\Filament\Resources\SleeveTypes\SleeveTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSleeveType extends EditRecord
{
    protected static string $resource = SleeveTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
