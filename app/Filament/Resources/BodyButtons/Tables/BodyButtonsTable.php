<?php

namespace App\Filament\Resources\BodyButtons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BodyButtonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bodyType.name')
                    ->label('Body Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('bodyType.code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('buttonImage.name')
                    ->label('Button Image')
                    ->searchable()
                    ->sortable(),

                ImageColumn::make('image')
                    ->label('Image'),

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
