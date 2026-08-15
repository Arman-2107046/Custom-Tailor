<?php

namespace App\Filament\Resources\LapelSubcategories\Pages;

use App\Filament\Resources\LapelSubcategories\LapelSubcategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLapelSubcategory extends EditRecord
{
    protected static string $resource = LapelSubcategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
