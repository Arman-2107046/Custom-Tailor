<?php

namespace App\Filament\Resources\DefaultLinings\Pages;

use App\Filament\Resources\DefaultLinings\DefaultLiningResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDefaultLinings extends ListRecords
{
    protected static string $resource = DefaultLiningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
