<?php

namespace App\Filament\Imports;

use App\Models\Fabric;
use App\Models\Sleeve;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class SleeveImporter extends Importer
{
    protected static ?string $model = Sleeve::class;

    protected static ?array $fabricMap = null;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('fabric')
                ->label('Fabric')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('name')
                ->label('Sleeve Name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('image')
                ->label('Sleeve Image')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('diagram')
                ->label('Sleeve Diagram')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

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

    public function resolveRecord(): ?Sleeve
    {
        if (static::$fabricMap === null) {
            static::$fabricMap = Fabric::pluck('id', 'name')->toArray();
        }

        $fabricId = static::$fabricMap[$this->data['fabric']] ?? null;

        if (! $fabricId) {
            throw new \Exception(
                "Fabric '{$this->data['fabric']}' not found."
            );
        }

        return Sleeve::updateOrCreate(
            [
                'fabric_id' => $fabricId,
                'name' => $this->data['name'],
            ],
            [
                'image' => $this->data['image'],
                'diagram' => $this->data['diagram'],
                'is_default' => (bool) $this->data['is_default'],
                'status' => (bool) $this->data['status'],
                'layer_index' => 160,

                // layer_index is omitted because the database default (100) is used.
            ]
        );
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your sleeve import completed successfully. '
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
