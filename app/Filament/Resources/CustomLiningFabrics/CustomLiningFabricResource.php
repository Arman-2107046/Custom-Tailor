<?php

namespace App\Filament\Resources\CustomLiningFabrics;

use App\Filament\Resources\CustomLiningFabrics\Pages\CreateCustomLiningFabric;
use App\Filament\Resources\CustomLiningFabrics\Pages\EditCustomLiningFabric;
use App\Filament\Resources\CustomLiningFabrics\Pages\ListCustomLiningFabrics;
use App\Filament\Resources\CustomLiningFabrics\Schemas\CustomLiningFabricForm;
use App\Filament\Resources\CustomLiningFabrics\Tables\CustomLiningFabricsTable;
use App\Models\CustomLiningFabric;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomLiningFabricResource extends Resource
{
    protected static ?string $model = CustomLiningFabric::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CustomLiningFabricForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomLiningFabricsTable::configure($table);
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
            'index' => ListCustomLiningFabrics::route('/'),
            'create' => CreateCustomLiningFabric::route('/create'),
            'edit' => EditCustomLiningFabric::route('/{record}/edit'),
        ];
    }
}
