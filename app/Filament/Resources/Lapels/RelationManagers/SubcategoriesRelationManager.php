<?php

namespace App\Filament\Resources\Lapels\RelationManagers;

use App\Filament\Resources\LapelSubcategories\LapelSubcategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class SubcategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'subcategories';

    protected static ?string $relatedResource = LapelSubcategoryResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
