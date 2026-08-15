<?php

namespace App\Filament\Resources\LiningTypes\Pages;

use App\Filament\Resources\LiningTypes\LiningTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLiningTypes extends ListRecords
{
    protected static string $resource = LiningTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
