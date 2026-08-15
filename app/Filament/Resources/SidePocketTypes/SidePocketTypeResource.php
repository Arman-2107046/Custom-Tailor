<?php

namespace App\Filament\Resources\SidePocketTypes;

use App\Filament\Resources\SidePocketTypes\Pages\CreateSidePocketType;
use App\Filament\Resources\SidePocketTypes\Pages\EditSidePocketType;
use App\Filament\Resources\SidePocketTypes\Pages\ListSidePocketTypes;
use App\Filament\Resources\SidePocketTypes\Schemas\SidePocketTypeForm;
use App\Filament\Resources\SidePocketTypes\Tables\SidePocketTypesTable;
use App\Models\SidePocketType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SidePocketTypeResource extends Resource
{
    protected static ?string $model = SidePocketType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SidePocketTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SidePocketTypesTable::configure($table);
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
            'index' => ListSidePocketTypes::route('/'),
            'create' => CreateSidePocketType::route('/create'),
            'edit' => EditSidePocketType::route('/{record}/edit'),
        ];
    }
}
