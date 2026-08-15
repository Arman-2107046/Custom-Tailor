<?php

namespace App\Filament\Resources\Lapels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LapelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fabric.name')
                    ->searchable(),
                TextColumn::make('body.name')
                    ->searchable(),
                TextColumn::make('lapelCategory.name')
                    ->searchable(),
                TextColumn::make('lapelSubcategory.name')
                    ->searchable(),
                ImageColumn::make('image'),
                TextColumn::make('layer_index')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->boolean(),
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
