<?php

namespace App\Filament\Resources\ButtonImages\Pages;

use App\Filament\Resources\ButtonImages\ButtonImageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditButtonImage extends EditRecord
{
    protected static string $resource = ButtonImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
