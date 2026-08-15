<?php

namespace App\Filament\Imports;

use App\Models\Fabric;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class FabricImporter extends Importer
{
    protected static ?string $model = Fabric::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('price')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),

            ImportColumn::make('image')
                ->requiredMapping()
                ->rules(['required', 'url', 'max:2048']),

            ImportColumn::make('is_default')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),

            ImportColumn::make('status')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean']),
        ];
    }

    public function resolveRecord(): ?Fabric
    {
        return Fabric::updateOrCreate(
            [
                'name' => $this->data['name'],
            ],
            [
                'price' => $this->data['price'],
                'image' => $this->data['image'],
                'is_default' => (bool) $this->data['is_default'],
                'status' => (bool) $this->data['status'],
            ]
        );
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your fabric import completed successfully. '
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
