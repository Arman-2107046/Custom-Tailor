<?php

namespace App\Filament\Imports;

use App\Models\Fabric;
use App\Models\Lining;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class LiningImporter extends Importer
{
    protected static ?string $model = Lining::class;

    protected static ?array $fabricMap = null;

    public static function getColumns(): array
    {
        return [

            ImportColumn::make('fabric')
                ->label('Fabric')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                ]),

            ImportColumn::make('name')
                ->label('Lining Name')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('fabric_image')
                ->label('Fabric Rendering Image')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('image')
                ->label('Lining Image')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('layer_index')
                ->label('Layer Index')
                ->requiredMapping()
                ->numeric()
                ->rules([
                    'required',
                    'integer',
                    'min:0',
                ]),

            ImportColumn::make('is_default')
                ->requiredMapping()
                ->boolean()
                ->rules([
                    'required',
                    'boolean',
                ]),
        ];
    }

    public function resolveRecord(): ?Lining
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

        return Lining::updateOrCreate(

            [
                'fabric_id' => $fabricId,
                'name' => $this->data['name'],
            ],

            [
                'fabric_image' => $this->data['fabric_image'],
                'image' => $this->data['image'],
                'layer_index' => (int) $this->data['layer_index'],
                'is_default' => (bool) $this->data['is_default'],
            ]
        );
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your lining import completed successfully. '
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
