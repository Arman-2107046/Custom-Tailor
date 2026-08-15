<?php

namespace App\Services\BulkUpload;

use App\Models\LiningType;

class LiningTypesUploader
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
         * LT_Satin Lining.png
         *
         * becomes:
         *
         * LT_Satin Lining
         */
        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );


        /*
         * Remove identifier
         *
         * LT_Satin Lining
         *
         * becomes:
         *
         * Satin Lining
         */
        $filename = preg_replace(
            '/^LT_/',
            '',
            $filename
        );


        $name = trim($filename);


        if (! $name) {
            return [
                'success' => false,
                'message' => $originalName . ' — invalid name',
            ];
        }


        /*
         * Create or update lining type
         */
        LiningType::updateOrCreate(
            [
                'name' => $name,
            ],
            [
                'diagram' => $url,
            ]
        );


        return [
            'success' => true,
            'message' => "{$name} imported successfully",
        ];
    }
}
