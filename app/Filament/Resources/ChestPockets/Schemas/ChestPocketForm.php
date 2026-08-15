<?php

namespace App\Filament\Resources\ChestPockets\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ChestPocketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('fabric_id')
                    ->relationship('fabric', 'name')
                    ->required(),
                Select::make('chest_pocket_type_id')
                    ->relationship('chestPocketType', 'name')
                    ->required(),
                TextInput::make('image')
                    ->url()
                    ->required(),
                TextInput::make('layer_index')
                    ->required()
                    ->numeric()
                    ->default(100),
                Toggle::make('is_default')
                    ->required(),
                Toggle::make('status')
                    ->required(),
            ]);
    }
}
