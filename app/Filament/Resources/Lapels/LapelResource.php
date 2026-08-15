<?php

namespace App\Filament\Resources\Lapels;

use App\Filament\Resources\Lapels\Pages\CreateLapel;
use App\Filament\Resources\Lapels\Pages\EditLapel;
use App\Filament\Resources\Lapels\Pages\ListLapels;
use App\Filament\Resources\Lapels\Schemas\LapelForm;
use App\Filament\Resources\Lapels\Tables\LapelsTable;
use App\Models\Lapel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LapelResource extends Resource
{
    protected static ?string $model = Lapel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LapelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LapelsTable::configure($table);
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
            'index' => ListLapels::route('/'),
            'create' => CreateLapel::route('/create'),
            'edit' => EditLapel::route('/{record}/edit'),
        ];
    }
}
