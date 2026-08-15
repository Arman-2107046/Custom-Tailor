<?php

namespace App\Filament\Resources\BodyTypes\Pages;

use App\Filament\Resources\BodyTypes\BodyTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBodyType extends EditRecord
{
    protected static string $resource = BodyTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
