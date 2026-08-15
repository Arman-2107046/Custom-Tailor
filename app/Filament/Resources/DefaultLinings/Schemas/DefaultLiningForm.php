<?php

namespace App\Filament\Resources\DefaultLinings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DefaultLiningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('fabric_id')
                    ->relationship('fabric', 'name')
                    ->required(),

                Select::make('body_type_id')
                    ->relationship('bodyType', 'name')
                    ->required(),

                Select::make('lining_type_id')
                    ->relationship('liningType', 'name')
                    ->required(),

                TextInput::make('image')
                    ->url()
                    ->required(),

                TextInput::make('layer_index')
                    ->required()
                    ->numeric()
                    ->default(0),

                Toggle::make('status')
                    ->default(true)
                    ->required(),
            ]);
    }
}
