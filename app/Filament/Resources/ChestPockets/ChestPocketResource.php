<?php

namespace App\Filament\Resources\ChestPockets;

use App\Filament\Resources\ChestPockets\Pages\CreateChestPocket;
use App\Filament\Resources\ChestPockets\Pages\EditChestPocket;
use App\Filament\Resources\ChestPockets\Pages\ListChestPockets;
use App\Filament\Resources\ChestPockets\Schemas\ChestPocketForm;
use App\Filament\Resources\ChestPockets\Tables\ChestPocketsTable;
use App\Models\ChestPocket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChestPocketResource extends Resource
{
    protected static ?string $model = ChestPocket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ChestPocketForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChestPocketsTable::configure($table);
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
            'index' => ListChestPockets::route('/'),
            'create' => CreateChestPocket::route('/create'),
            'edit' => EditChestPocket::route('/{record}/edit'),
        ];
    }
}
