<?php

namespace App\Filament\Resources\CustomLinings;

use App\Filament\Resources\CustomLinings\Pages\CreateCustomLining;
use App\Filament\Resources\CustomLinings\Pages\EditCustomLining;
use App\Filament\Resources\CustomLinings\Pages\ListCustomLinings;
use App\Filament\Resources\CustomLinings\Schemas\CustomLiningForm;
use App\Filament\Resources\CustomLinings\Tables\CustomLiningsTable;
use App\Models\CustomLining;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomLiningResource extends Resource
{
    protected static ?string $model = CustomLining::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CustomLiningForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomLiningsTable::configure($table);
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
            'index' => ListCustomLinings::route('/'),
            'create' => CreateCustomLining::route('/create'),
            'edit' => EditCustomLining::route('/{record}/edit'),
        ];
    }
}
