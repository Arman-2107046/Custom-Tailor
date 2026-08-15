<?php

namespace App\Filament\Resources\Sleeves\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SleeveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('fabric_id')
                    ->relationship('fabric', 'name')
                    ->required(),
                Select::make('sleeve_type_id')
                    ->relationship('sleeveType', 'name')
                    ->required(),
                TextInput::make('image')
                    ->url()
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
