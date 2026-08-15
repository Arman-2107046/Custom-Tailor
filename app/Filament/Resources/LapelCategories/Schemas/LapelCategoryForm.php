<?php

namespace App\Filament\Resources\LapelCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LapelCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                TextInput::make('diagram')
                    ->url()
                    ->required(),

                Toggle::make('status')
                    ->default(true)
                    ->required(),

                Toggle::make('is_default')
                    ->default(false)
                    ->required(),
            ]);
    }
}
