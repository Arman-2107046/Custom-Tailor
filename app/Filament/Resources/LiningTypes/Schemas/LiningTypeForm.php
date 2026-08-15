<?php

namespace App\Filament\Resources\LiningTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LiningTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('diagram')
                    ->required()
                    ->url(),
            ]);
    }
}
