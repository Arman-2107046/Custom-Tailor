<?php

namespace App\Filament\Imports;

use App\Models\ButtonImage;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ButtonImageImporter extends Importer
{
    protected static ?string $model = ButtonImage::class;

    public static function getColumns(): array
    {
        return [

            ImportColumn::make('name')
                ->label('Button Name')
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

        ];
    }

    public function resolveRecord(): ?ButtonImage
    {
        return ButtonImage::updateOrCreate(

            [
                'name' => $this->data['name'],
            ],

            [
                'diagram' => $this->data['diagram'],
            ]

        );
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your button image import completed successfully. '
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
