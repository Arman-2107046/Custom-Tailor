<?php

namespace App\Filament\Resources\ChestPocketTypes;

use App\Filament\Resources\ChestPocketTypes\Pages\CreateChestPocketType;
use App\Filament\Resources\ChestPocketTypes\Pages\EditChestPocketType;
use App\Filament\Resources\ChestPocketTypes\Pages\ListChestPocketTypes;
use App\Filament\Resources\ChestPocketTypes\Schemas\ChestPocketTypeForm;
use App\Filament\Resources\ChestPocketTypes\Tables\ChestPocketTypesTable;
use App\Models\ChestPocketType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChestPocketTypeResource extends Resource
{
    protected static ?string $model = ChestPocketType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ChestPocketTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChestPocketTypesTable::configure($table);
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
            'index' => ListChestPocketTypes::route('/'),
            'create' => CreateChestPocketType::route('/create'),
            'edit' => EditChestPocketType::route('/{record}/edit'),
        ];
    }
}
