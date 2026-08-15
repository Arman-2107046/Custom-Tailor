<?php

namespace App\Filament\Resources\DefaultLinings;

use App\Filament\Resources\DefaultLinings\Pages\CreateDefaultLining;
use App\Filament\Resources\DefaultLinings\Pages\EditDefaultLining;
use App\Filament\Resources\DefaultLinings\Pages\ListDefaultLinings;
use App\Filament\Resources\DefaultLinings\Schemas\DefaultLiningForm;
use App\Filament\Resources\DefaultLinings\Tables\DefaultLiningsTable;
use App\Models\DefaultLining;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DefaultLiningResource extends Resource
{
    protected static ?string $model = DefaultLining::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DefaultLiningForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DefaultLiningsTable::configure($table);
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
            'index' => ListDefaultLinings::route('/'),
            'create' => CreateDefaultLining::route('/create'),
            'edit' => EditDefaultLining::route('/{record}/edit'),
        ];
    }
}
