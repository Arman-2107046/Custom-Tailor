<?php

namespace App\Filament\Resources\LiningTypes;

use App\Filament\Resources\LiningTypes\Pages\CreateLiningType;
use App\Filament\Resources\LiningTypes\Pages\EditLiningType;
use App\Filament\Resources\LiningTypes\Pages\ListLiningTypes;
use App\Filament\Resources\LiningTypes\Schemas\LiningTypeForm;
use App\Filament\Resources\LiningTypes\Tables\LiningTypesTable;
use App\Models\LiningType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LiningTypeResource extends Resource
{
    protected static ?string $model = LiningType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LiningTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LiningTypesTable::configure($table);
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
            'index' => ListLiningTypes::route('/'),
            'create' => CreateLiningType::route('/create'),
            'edit' => EditLiningType::route('/{record}/edit'),
        ];
    }
}
