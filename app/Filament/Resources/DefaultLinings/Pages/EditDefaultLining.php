<?php

namespace App\Filament\Resources\DefaultLinings\Pages;

use App\Filament\Resources\DefaultLinings\DefaultLiningResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDefaultLining extends EditRecord
{
    protected static string $resource = DefaultLiningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
