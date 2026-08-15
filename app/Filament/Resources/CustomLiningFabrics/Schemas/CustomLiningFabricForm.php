<?php

namespace App\Filament\Resources\CustomLiningFabrics\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomLiningFabricForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('image')
                    ->url()
                    ->required(),
                Toggle::make('status')
                    ->required(),
            ]);
    }
}
