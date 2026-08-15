<?php

namespace App\Filament\Imports;

use App\Models\Body;
use App\Models\Fabric;
use App\Models\Lapel;
use App\Models\LapelCategory;
use App\Models\LapelSubCategory;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class LapelImporter extends Importer
{
    protected static ?string $model = Lapel::class;

    protected static ?array $fabricMap = null;
    protected static ?array $bodyMap = null;
    protected static ?array $categoryMap = null;
    protected static ?array $subcategoryMap = null;

    public static function getColumns(): array
    {
        return [

            ImportColumn::make('fabric')
                ->requiredMapping()
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('body')
                ->requiredMapping()
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('lapel_category')
                ->requiredMapping()
                ->fillRecordUsing(fn () => null),

            ImportColumn::make('lapel_subcategory')
                ->requiredMapping()
                ->fillRecordUsing(fn () => null),

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

    public function resolveRecord(): Lapel
    {
        static::$fabricMap ??= Fabric::pluck('id', 'name')->toArray();

        static::$bodyMap ??= Body::pluck('id', 'name')->toArray();

        static::$categoryMap ??= LapelCategory::pluck('id', 'name')->toArray();

        static::$subcategoryMap ??= LapelSubCategory::pluck('id', 'name')->toArray();

        $fabricId = static::$fabricMap[$this->data['fabric']] ?? null;
        $bodyId = static::$bodyMap[$this->data['body']] ?? null;
        $categoryId = static::$categoryMap[$this->data['lapel_category']] ?? null;
        $subcategoryId = static::$subcategoryMap[$this->data['lapel_subcategory']] ?? null;

        if (! $fabricId) {
            throw new \Exception("Fabric '{$this->data['fabric']}' not found.");
        }

        if (! $bodyId) {
            throw new \Exception("Body '{$this->data['body']}' not found.");
        }

        if (! $categoryId) {
            throw new \Exception("Lapel category '{$this->data['lapel_category']}' not found.");
        }

        if (! $subcategoryId) {
            throw new \Exception("Lapel subcategory '{$this->data['lapel_subcategory']}' not found.");
        }

        return new Lapel([
            'fabric_id' => $fabricId,
            'body_id' => $bodyId,
            'lapel_category_id' => $categoryId,
            'lapel_subcategory_id' => $subcategoryId,
            'image' => $this->data['image'],
            'layer_index' => 150,
            'is_default' => (bool) $this->data['is_default'],
            'status' => (bool) $this->data['status'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your lapel import completed successfully. '
            . Number::format($import->successful_rows)
            . ' ' . str('row')->plural($import->successful_rows)
            . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '
                . Number::format($failedRowsCount)
                . ' ' . str('row')->plural($failedRowsCount)
                . ' failed.';
        }

        return $body;
    }
}
