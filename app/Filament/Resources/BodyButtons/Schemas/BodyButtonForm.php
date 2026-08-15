<?php

namespace App\Filament\Resources\BodyButtons\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BodyButtonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('body_type_id')
                    ->relationship('bodyType', 'code')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('button_image_id')
                    ->relationship('buttonImage', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('image')
                    ->url()
                    ->required(),

                TextInput::make('layer_index')
                    ->numeric()
                    ->default(160)
                    ->required(),

                Toggle::make('is_default')
                    ->default(false)
                    ->required(),

                Toggle::make('status')
                    ->default(true)
                    ->required(),
            ]);
    }
}
