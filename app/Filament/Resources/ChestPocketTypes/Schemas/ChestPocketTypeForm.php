<?php

namespace App\Filament\Resources\ChestPocketTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ChestPocketTypeForm
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
                    ->required()
                    ->url(),
            ]);
    }
}
