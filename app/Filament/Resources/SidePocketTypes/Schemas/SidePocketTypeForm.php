<?php

namespace App\Filament\Resources\SidePocketTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SidePocketTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('diagram')
                    ->url()
                    ->required(),
            ]);
    }
}
