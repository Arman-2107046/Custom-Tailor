<?php

namespace App\Filament\Resources\SleeveTypes\Pages;

use App\Filament\Resources\SleeveTypes\SleeveTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSleeveTypes extends ListRecords
{
    protected static string $resource = SleeveTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
