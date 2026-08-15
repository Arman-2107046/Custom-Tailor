<?php

namespace App\Services\BulkUpload;

use App\Models\SleeveType;

class SleeveTypesUploader
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
         * SLT_English_Shoulder_ES.png
         *
         * becomes:
         *
         * SLT_English_Shoulder_ES
         */

        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );


        /*
         * Remove identifier
         *
         * SLT_English_Shoulder_ES
         *
         * becomes:
         *
         * English_Shoulder_ES
         */

        $filename = preg_replace(
            '/^SLT_/',
            '',
            $filename
        );


        /*
         * Split filename
         *
         * English_Shoulder_ES
         */

        $parts = explode('_', $filename);


        if (count($parts) < 2) {
            return [
                'success' => false,
                'message' => $originalName . ' — invalid format',
            ];
        }


        /*
         * Last part = code
         */

        $code = array_pop($parts);


        /*
         * Remaining parts = name
         */

        $name = implode(' ', $parts);


        $name = trim($name);
        $code = trim($code);


        if ($name === '' || $code === '') {
            return [
                'success' => false,
                'message' => $originalName . ' — missing name/code',
            ];
        }


        SleeveType::updateOrCreate(
            [
                'code' => $code,
            ],
            [
                'name' => $name,
                'diagram' => $url,
            ]
        );


        return [
            'success' => true,
            'message' => "{$name} imported successfully",
        ];
    }
}
