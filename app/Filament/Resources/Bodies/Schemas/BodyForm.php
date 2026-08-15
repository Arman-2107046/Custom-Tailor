<?php

namespace App\Filament\Resources\Bodies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BodyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('fabric_id')
                    ->label('Fabric')
                    ->relationship('fabric', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('body_type_id')
                    ->label('Body Type')
                    ->relationship('bodyType', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('image')
                    ->label('Body Image URL')
                    ->url()
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('layer_index')
                    ->label('Layer Index')
                    ->numeric()
                    ->default(100)
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                Toggle::make('is_default')
                    ->label('Default Body for this Fabric')
                    ->default(false)
                    ->required(),

                Toggle::make('status')
                    ->label('Active')
                    ->default(true)
                    ->required(),
            ])
            ->columns(2);
    }
}
