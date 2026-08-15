<?php

namespace App\Filament\Resources\CustomLinings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomLiningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                /*
                |--------------------------------------------------------------------------
                | Original Fabric
                |--------------------------------------------------------------------------
                */

                TextColumn::make('fabric.name')
                    ->label('Fabric')
                    ->searchable()
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Lining Type
                |--------------------------------------------------------------------------
                */

                TextColumn::make('liningType.name')
                    ->label('Lining Type')
                    ->searchable()
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Custom Lining Fabric
                |--------------------------------------------------------------------------
                */

                TextColumn::make('customLiningFabric.name')
                    ->label('Custom Lining Fabric')
                    ->searchable()
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Image
                |--------------------------------------------------------------------------
                */

                ImageColumn::make('image')
                    ->label('Image'),

                /*
                |--------------------------------------------------------------------------
                | Layer Index
                |--------------------------------------------------------------------------
                */

                TextColumn::make('layer_index')
                    ->numeric()
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | Default
                |--------------------------------------------------------------------------
                */

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                IconColumn::make('status')
                    ->label('Status')
                    ->boolean(),

                /*
                |--------------------------------------------------------------------------
                | Created At
                |--------------------------------------------------------------------------
                */

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                /*
                |--------------------------------------------------------------------------
                | Updated At
                |--------------------------------------------------------------------------
                */

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                //
            ])

            ->recordActions([
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
