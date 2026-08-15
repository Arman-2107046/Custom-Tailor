<?php

namespace App\Filament\Imports;

use App\Models\Body;
use App\Models\Fabric;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class BodyImporter extends Importer
{
    protected static ?string $model = Body::class;

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
                ->label('Body Name')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('image')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('diagram')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('is_default')
                ->requiredMapping()
                ->boolean()
                ->rules([
                    'required',
                    'boolean',
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


    public function resolveRecord(): ?Body
    {
        // Cache fabric names and IDs
        if (static::$fabricMap === null) {
            static::$fabricMap = Fabric::pluck('id', 'name')->toArray();
        }


        $fabricId = static::$fabricMap[$this->data['fabric']] ?? null;


        if (! $fabricId) {
            throw new \Exception(
                "Fabric '{$this->data['fabric']}' not found."
            );
        }


        return Body::updateOrCreate(
            [
                'fabric_id' => $fabricId,
                'name'      => $this->data['name'],
            ],
            [
                'image'       => $this->data['image'],
                'diagram'     => $this->data['diagram'],
                'layer_index' => 100,
                'is_default'  => (bool) $this->data['is_default'],
                'status'      => (bool) $this->data['status'],
            ]
        );
    }


    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your body import completed successfully. '
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
