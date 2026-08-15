<?php

namespace App\Services\BulkUpload;

use App\Models\LapelSubCategory;

class LapelSubcategoriesUploader
{
    public function handle(array $file): array
    {
        $originalName = $file['name'] ?? null;
        $url = $file['url'] ?? null;

        if (! $originalName || ! $url) {
            return [
                'success' => false,
                'message' => 'Invalid file data',
            ];
        }

        /*
         * Remove extension
         *
         * LPS_Classic.png
         *
         * becomes:
         *
         * LPS_Classic
         */
        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );

        /*
         * Remove identifier
         *
         * LPS_Classic
         *
         * becomes:
         *
         * Classic
         */
        $name = preg_replace(
            '/^LPS_/',
            '',
            $filename
        );

        $name = trim(
            str_replace('_', ' ', $name)
        );

        if ($name === '') {
            return [
                'success' => false,
                'message' => $originalName . ' — invalid subcategory name',
            ];
        }

        LapelSubCategory::updateOrCreate(
            [
                'name' => $name,
            ],
            [
                'diagram' => $url,
                'is_default' => false,
            ]
        );

        return [
            'success' => true,
            'message' => "{$name} imported successfully",
        ];
    }
}
