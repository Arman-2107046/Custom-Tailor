<?php

namespace App\Filament\Resources\BodyButtons;

use App\Filament\Resources\BodyButtons\Pages\CreateBodyButton;
use App\Filament\Resources\BodyButtons\Pages\EditBodyButton;
use App\Filament\Resources\BodyButtons\Pages\ListBodyButtons;
use App\Filament\Resources\BodyButtons\Schemas\BodyButtonForm;
use App\Filament\Resources\BodyButtons\Tables\BodyButtonsTable;
use App\Models\BodyButton;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BodyButtonResource extends Resource
{
    protected static ?string $model = BodyButton::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BodyButtonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BodyButtonsTable::configure($table);
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
            'index' => ListBodyButtons::route('/'),
            'create' => CreateBodyButton::route('/create'),
            'edit' => EditBodyButton::route('/{record}/edit'),
        ];
    }
}
