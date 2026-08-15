<?php

namespace App\Filament\Resources\CustomLiningFabrics\Pages;

use App\Filament\Resources\CustomLiningFabrics\CustomLiningFabricResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomLiningFabrics extends ListRecords
{
    protected static string $resource = CustomLiningFabricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
