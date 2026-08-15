<?php

namespace App\Filament\Resources\CustomLinings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomLiningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Original Fabric
                |--------------------------------------------------------------------------
                */

                Select::make('fabric_id')
                    ->relationship('fabric', 'name')
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | Custom Lining Fabric
                |--------------------------------------------------------------------------
                */

                Select::make('custom_lining_fabric_id')
                    ->relationship('customLiningFabric', 'name')
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | Lining Type
                |--------------------------------------------------------------------------
                */

                Select::make('lining_type_id')
                    ->relationship('liningType', 'name')
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | Image
                |--------------------------------------------------------------------------
                */

                TextInput::make('image')
                    ->url()
                    ->required(),

                /*
                |--------------------------------------------------------------------------
                | Layer Index
                |--------------------------------------------------------------------------
                */

                TextInput::make('layer_index')
                    ->required()
                    ->numeric()
                    ->default(100),

                /*
                |--------------------------------------------------------------------------
                | Default
                |--------------------------------------------------------------------------
                */

                Toggle::make('is_default')
                    ->required()
                    ->default(false),

                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                Toggle::make('status')
                    ->required()
                    ->default(true),
            ]);
    }
}
