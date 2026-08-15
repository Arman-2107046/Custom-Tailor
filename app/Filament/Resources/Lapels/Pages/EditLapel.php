<?php

namespace App\Filament\Resources\Lapels\Pages;

use App\Filament\Resources\Lapels\LapelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLapel extends EditRecord
{
    protected static string $resource = LapelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
