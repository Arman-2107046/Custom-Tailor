<?php

namespace App\Filament\Resources\BodyTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BodyTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('code')
                    ->label('Code')
                    ->helperText('Examples: SB1, SB2, DB4, DB6')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),

                TextInput::make('diagram')
                    ->url()
                    ->required(),
            ]);
    }
}
