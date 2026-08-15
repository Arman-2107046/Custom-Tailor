<?php

namespace App\Filament\Resources\CustomLinings\Pages;

use App\Filament\Resources\CustomLinings\CustomLiningResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomLining extends EditRecord
{
    protected static string $resource = CustomLiningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
