<?php

namespace App\Filament\Resources\LapelCategories\Pages;

use App\Filament\Resources\LapelCategories\LapelCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLapelCategory extends EditRecord
{
    protected static string $resource = LapelCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
