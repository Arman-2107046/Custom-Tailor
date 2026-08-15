<?php

namespace App\Services\BulkUpload;

use App\Models\LapelCategory;

class LapelCategoriesUploader
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
         * LPC_Notch.png
         *
         * becomes:
         *
         * LPC_Notch
         */
        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );

        /*
         * Remove identifier
         *
         * LPC_Notch
         *
         * becomes:
         *
         * Notch
         */
        $name = preg_replace(
            '/^LPC_/',
            '',
            $filename
        );

        $name = trim(
            str_replace('_', ' ', $name)
        );

        if ($name === '') {
            return [
                'success' => false,
                'message' => $originalName . ' — invalid category name',
            ];
        }

        LapelCategory::updateOrCreate(
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
