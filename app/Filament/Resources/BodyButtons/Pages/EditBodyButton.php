<?php

namespace App\Filament\Resources\BodyButtons\Pages;

use App\Filament\Resources\BodyButtons\BodyButtonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBodyButton extends EditRecord
{
    protected static string $resource = BodyButtonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
