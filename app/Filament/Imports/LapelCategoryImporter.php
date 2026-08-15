<?php

namespace App\Filament\Imports;

use App\Models\LapelCategory;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class LapelCategoryImporter extends Importer
{
    protected static ?string $model = LapelCategory::class;


    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Category Name')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('diagram')
                ->label('Diagram')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('status')
                ->requiredMapping()
                ->boolean()
                ->rules([
                    'required',
                    'boolean',
                ]),
        ];
    }


    public function resolveRecord(): LapelCategory
    {
        return new LapelCategory();
    }


    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your lapel category import has completed and '
            . Number::format($import->successful_rows)
            . ' '
            . str('row')->plural($import->successful_rows)
            . ' imported.';


        if ($failedRowsCount = $import->getFailedRowsCount()) {

            $body .= ' '
                . Number::format($failedRowsCount)
                . ' '
                . str('row')->plural($failedRowsCount)
                . ' failed.';
        }


        return $body;
    }
}
