<?php

namespace App\Filament\Imports;

use App\Models\LapelCategory;
use App\Models\LapelSubCategory;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class LapelSubCategoryImporter extends Importer
{
    protected static ?string $model = LapelSubCategory::class;


    protected static ?array $categoryMap = null;


    public static function getColumns(): array
    {
        return [

            ImportColumn::make('lapel_category')
                ->label('Lapel Category')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                ]),


            ImportColumn::make('name')
                ->label('Subcategory Name')
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


    public function resolveRecord(): LapelSubCategory
    {
        if (static::$categoryMap === null) {

            static::$categoryMap = LapelCategory::pluck(
                'id',
                'name'
            )->toArray();

        }


        $categoryId = static::$categoryMap[$this->data['lapel_category']] ?? null;


        if (! $categoryId) {

            throw new \Exception(
                "Lapel category '{$this->data['lapel_category']}' not found."
            );

        }


        return LapelSubCategory::create([

            'lapel_category_id' => $categoryId,

            'name' => $this->data['name'],

            'diagram' => $this->data['diagram'],

            'status' => (bool) $this->data['status'],

        ]);
    }


    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your lapel subcategory import completed successfully. '
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
