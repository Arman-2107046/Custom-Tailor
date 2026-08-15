<?php

namespace App\Filament\Resources\SleeveTypes;

use App\Filament\Resources\SleeveTypes\Pages\CreateSleeveType;
use App\Filament\Resources\SleeveTypes\Pages\EditSleeveType;
use App\Filament\Resources\SleeveTypes\Pages\ListSleeveTypes;
use App\Filament\Resources\SleeveTypes\Schemas\SleeveTypeForm;
use App\Filament\Resources\SleeveTypes\Tables\SleeveTypesTable;
use App\Models\SleeveType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SleeveTypeResource extends Resource
{
    protected static ?string $model = SleeveType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SleeveTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SleeveTypesTable::configure($table);
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
            'index' => ListSleeveTypes::route('/'),
            'create' => CreateSleeveType::route('/create'),
            'edit' => EditSleeveType::route('/{record}/edit'),
        ];
    }
}
