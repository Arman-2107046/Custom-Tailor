<?php

namespace App\Filament\Resources\ButtonImages;

use App\Filament\Resources\ButtonImages\Pages\CreateButtonImage;
use App\Filament\Resources\ButtonImages\Pages\EditButtonImage;
use App\Filament\Resources\ButtonImages\Pages\ListButtonImages;
use App\Filament\Resources\ButtonImages\Schemas\ButtonImageForm;
use App\Filament\Resources\ButtonImages\Tables\ButtonImagesTable;
use App\Models\ButtonImage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ButtonImageResource extends Resource
{
    protected static ?string $model = ButtonImage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ButtonImageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ButtonImagesTable::configure($table);
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
            'index' => ListButtonImages::route('/'),
            'create' => CreateButtonImage::route('/create'),
            'edit' => EditButtonImage::route('/{record}/edit'),
        ];
    }
}
