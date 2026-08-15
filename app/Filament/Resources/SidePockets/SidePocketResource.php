<?php

namespace App\Filament\Resources\SidePockets;

use App\Filament\Resources\SidePockets\Pages\CreateSidePocket;
use App\Filament\Resources\SidePockets\Pages\EditSidePocket;
use App\Filament\Resources\SidePockets\Pages\ListSidePockets;
use App\Filament\Resources\SidePockets\Schemas\SidePocketForm;
use App\Filament\Resources\SidePockets\Tables\SidePocketsTable;
use App\Models\SidePocket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SidePocketResource extends Resource
{
    protected static ?string $model = SidePocket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SidePocketForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SidePocketsTable::configure($table);
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
            'index' => ListSidePockets::route('/'),
            'create' => CreateSidePocket::route('/create'),
            'edit' => EditSidePocket::route('/{record}/edit'),
        ];
    }
}
