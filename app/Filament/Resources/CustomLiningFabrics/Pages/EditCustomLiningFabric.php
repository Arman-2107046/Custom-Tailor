<?php

namespace App\Filament\Resources\CustomLiningFabrics\Pages;

use App\Filament\Resources\CustomLiningFabrics\CustomLiningFabricResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomLiningFabric extends EditRecord
{
    protected static string $resource = CustomLiningFabricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
