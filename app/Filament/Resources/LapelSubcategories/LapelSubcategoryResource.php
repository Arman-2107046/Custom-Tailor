<?php

namespace App\Filament\Resources\LapelSubcategories;

use App\Filament\Resources\LapelSubcategories\Pages\CreateLapelSubcategory;
use App\Filament\Resources\LapelSubcategories\Pages\EditLapelSubcategory;
use App\Filament\Resources\LapelSubcategories\Pages\ListLapelSubcategories;
use App\Filament\Resources\LapelSubcategories\Schemas\LapelSubcategoryForm;
use App\Filament\Resources\LapelSubcategories\Tables\LapelSubcategoriesTable;
use App\Models\LapelSubCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LapelSubcategoryResource extends Resource
{
    protected static ?string $model = LapelSubCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LapelSubcategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LapelSubcategoriesTable::configure($table);
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
            'index' => ListLapelSubcategories::route('/'),
            'create' => CreateLapelSubcategory::route('/create'),
            'edit' => EditLapelSubcategory::route('/{record}/edit'),
        ];
    }
}
