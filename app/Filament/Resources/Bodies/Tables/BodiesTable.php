<?php

namespace App\Filament\Resources\Bodies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BodiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fabric.name')
                    ->label('Fabric')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bodyType.name')
                    ->label('Body Type')
                    ->searchable()
                    ->sortable(),

                ImageColumn::make('image')
                    ->label('Body Image')
                    ->square()
                    ->size(60),

                ImageColumn::make('bodyType.diagram')
                    ->label('Diagram')
                    ->square()
                    ->size(60),

                TextColumn::make('layer_index')
                    ->label('Layer')
                    ->badge()
                    ->sortable(),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                IconColumn::make('status')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('body_type_id')
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
