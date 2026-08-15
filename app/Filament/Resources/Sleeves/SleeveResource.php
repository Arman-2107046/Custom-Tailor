<?php

namespace App\Filament\Resources\Sleeves;

use App\Filament\Resources\Sleeves\Pages\CreateSleeve;
use App\Filament\Resources\Sleeves\Pages\EditSleeve;
use App\Filament\Resources\Sleeves\Pages\ListSleeves;
use App\Filament\Resources\Sleeves\Schemas\SleeveForm;
use App\Filament\Resources\Sleeves\Tables\SleevesTable;
use App\Models\Sleeve;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SleeveResource extends Resource
{
    protected static ?string $model = Sleeve::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SleeveForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SleevesTable::configure($table);
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
            'index' => ListSleeves::route('/'),
            'create' => CreateSleeve::route('/create'),
            'edit' => EditSleeve::route('/{record}/edit'),
        ];
    }
}
