<?php

namespace App\Filament\Resources\LiningTypes\Pages;

use App\Filament\Resources\LiningTypes\LiningTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLiningType extends EditRecord
{
    protected static string $resource = LiningTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
