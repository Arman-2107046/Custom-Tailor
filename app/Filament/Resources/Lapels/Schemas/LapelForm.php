<?php

namespace App\Filament\Resources\Lapels\Schemas;

use App\Models\Body;
use App\Models\LapelCategory;
use App\Models\LapelSubCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LapelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Fabric
                |--------------------------------------------------------------------------
                */

                Select::make('fabric_id')
                    ->label('Fabric')
                    ->relationship('fabric', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function ($set) {
                        // Body depends on Fabric.
                        $set('body_id', null);
                    }),


                /*
                |--------------------------------------------------------------------------
                | Body
                |--------------------------------------------------------------------------
                |
                | The bodies table does NOT have a "name" column.
                |
                | Body:
                |   id
                |   fabric_id
                |   body_type_id
                |
                | BodyType:
                |   id
                |   name
                |   code
                |
                | Therefore we load the BodyType relationship and
                | display BodyType.name.
                |
                */

                Select::make('body_id')
                    ->label('Body')
                    ->options(function ($get) {

                        $fabricId = $get('fabric_id');

                        if (! $fabricId) {
                            return [];
                        }

                        return Body::query()
                            ->with('bodyType')
                            ->where('fabric_id', $fabricId)
                            ->where('status', true)
                            ->get()
                            ->mapWithKeys(function (Body $body) {

                                return [
                                    $body->id => $body->bodyType?->name
                                        ?? 'Unknown Body',
                                ];

                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->disabled(
                        fn ($get) => blank($get('fabric_id'))
                    )
                    ->required(),


                /*
                |--------------------------------------------------------------------------
                | Lapel Category
                |--------------------------------------------------------------------------
                |
                | Category is independent from Subcategory.
                |
                | There is NO:
                |   lapel_category_id
                | inside lapel_sub_categories.
                |
                */

                Select::make('lapel_category_id')
                    ->label('Lapel Category')
                    ->options(function () {

                        return LapelCategory::query()
                            ->where('status', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required(),


                /*
                |--------------------------------------------------------------------------
                | Lapel Subcategory
                |--------------------------------------------------------------------------
                |
                | Subcategory is completely independent.
                |
                | DO NOT filter by lapel_category_id.
                |
                */

                Select::make('lapel_subcategory_id')
                    ->label('Lapel Subcategory')
                    ->options(function () {

                        return LapelSubCategory::query()
                            ->where('status', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->required(),


                /*
                |--------------------------------------------------------------------------
                | Image
                |--------------------------------------------------------------------------
                */

                TextInput::make('image')
                    ->label('Image URL')
                    ->required(),


                /*
                |--------------------------------------------------------------------------
                | Layer Index
                |--------------------------------------------------------------------------
                |
                | Default lapel layer = 150.
                |
                */

                TextInput::make('layer_index')
                    ->label('Layer Index')
                    ->default(150)
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required(),


                /*
                |--------------------------------------------------------------------------
                | Default
                |--------------------------------------------------------------------------
                */

                Toggle::make('is_default')
                    ->label('Default')
                    ->default(false),


                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                Toggle::make('status')
                    ->label('Active')
                    ->default(true),

            ]);
    }
}
