<?php

namespace App\Filament\Resources\LapelCategories;

use App\Filament\Resources\LapelCategories\Pages\CreateLapelCategory;
use App\Filament\Resources\LapelCategories\Pages\EditLapelCategory;
use App\Filament\Resources\LapelCategories\Pages\ListLapelCategories;
use App\Filament\Resources\LapelCategories\Schemas\LapelCategoryForm;
use App\Filament\Resources\LapelCategories\Tables\LapelCategoriesTable;
use App\Models\LapelCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LapelCategoryResource extends Resource
{
    protected static ?string $model = LapelCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LapelCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LapelCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLapelCategories::route('/'),
            'create' => CreateLapelCategory::route('/create'),
            'edit' => EditLapelCategory::route('/{record}/edit'),
        ];
    }
}
