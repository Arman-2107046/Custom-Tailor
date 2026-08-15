<?php

namespace App\Filament\Resources\DefaultLinings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DefaultLiningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fabric.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bodyType.name')
                    ->label('Body Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('liningType.name')
                    ->label('Lining Type')
                    ->searchable()
                    ->sortable(),

                ImageColumn::make('image'),

                TextColumn::make('layer_index')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('status')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
