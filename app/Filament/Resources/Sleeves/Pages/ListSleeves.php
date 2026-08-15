<?php

namespace App\Filament\Resources\Sleeves\Pages;

use App\Filament\Resources\Sleeves\SleeveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSleeves extends ListRecords
{
    protected static string $resource = SleeveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
