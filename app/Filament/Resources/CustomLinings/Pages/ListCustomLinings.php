<?php

namespace App\Filament\Resources\CustomLinings\Pages;

use App\Filament\Resources\CustomLinings\CustomLiningResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomLinings extends ListRecords
{
    protected static string $resource = CustomLiningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
