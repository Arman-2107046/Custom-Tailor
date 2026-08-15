<?php

namespace App\Filament\Resources\LapelSubcategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LapelSubcategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->label('Subcategory Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('diagram')
                    ->label('Diagram')
                    ->url(),

                Toggle::make('status')
                    ->default(true),

                Toggle::make('is_default')
                    ->label('Default')
                    ->default(false),

            ]);
    }
}
