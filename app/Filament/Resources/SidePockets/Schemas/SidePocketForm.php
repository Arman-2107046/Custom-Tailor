<?php

namespace App\Filament\Resources\SidePockets\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SidePocketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('fabric_id')
                    ->relationship('fabric', 'name')
                    ->required(),
                Select::make('side_pocket_type_id')
                    ->relationship('sidePocketType', 'name')
                    ->required(),
                TextInput::make('image')
                    ->activeUrl()
                    ->required(),
                TextInput::make('layer_index')
                    ->required()
                    ->numeric()
                    ->default(100),
                Toggle::make('is_default')
                    ->default(false)
                    ->required(),
                Toggle::make('status')
                    ->default(true)
                    ->required(),
            ]);
    }
}
