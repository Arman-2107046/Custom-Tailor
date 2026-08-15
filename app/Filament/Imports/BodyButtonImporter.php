<?php

namespace App\Filament\Imports;

use App\Models\Body;
use App\Models\BodyButton;
use App\Models\ButtonImage;
use App\Models\Fabric;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class BodyButtonImporter extends Importer
{
    protected static ?string $model = BodyButton::class;

    protected static ?array $fabricMap = null;
    protected static ?array $bodyMap = null;
    protected static ?array $buttonImageMap = null;

    public static function getColumns(): array
    {
        return [

            ImportColumn::make('fabric')
                ->requiredMapping(),

            ImportColumn::make('body')
                ->requiredMapping(),

            ImportColumn::make('button')
                ->label('Button')
                ->requiredMapping(),

            ImportColumn::make('image')
                ->requiredMapping(),

            ImportColumn::make('is_default')
                ->boolean()
                ->requiredMapping(),

            ImportColumn::make('status')
                ->boolean()
                ->requiredMapping(),

        ];
    }

    public function resolveRecord(): ?BodyButton
    {
        static::$fabricMap ??= Fabric::pluck('id', 'name')->toArray();
        static::$bodyMap ??= Body::pluck('id', 'name')->toArray();
        static::$buttonImageMap ??= ButtonImage::pluck('id', 'name')->toArray();

        $fabricId = static::$fabricMap[$this->data['fabric']] ?? null;
        $bodyId = static::$bodyMap[$this->data['body']] ?? null;
        $buttonImageId = static::$buttonImageMap[$this->data['button']] ?? null;

        if (! $fabricId) {
            throw new \Exception("Fabric '{$this->data['fabric']}' not found.");
        }

        if (! $bodyId) {
            throw new \Exception("Body '{$this->data['body']}' not found.");
        }

        if (! $buttonImageId) {
            throw new \Exception("Button '{$this->data['button']}' not found.");
        }

        return BodyButton::updateOrCreate(
            [
                'fabric_id' => $fabricId,
                'body_id' => $bodyId,
                'button_image_id' => $buttonImageId,
            ],
            [
                'image' => $this->data['image'],
                'layer_index' => 160,
                'is_default' => (bool) $this->data['is_default'],
                'status' => (bool) $this->data['status'],
            ]
        );
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your body button import completed successfully. '
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
