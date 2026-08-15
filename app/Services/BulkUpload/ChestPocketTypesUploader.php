<?php

namespace App\Services\BulkUpload;

use App\Models\ChestPocketType;

class ChestPocketTypesUploader
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
         * CPT_Welt_WELT.png
         *
         * becomes:
         *
         * CPT_Welt_WELT
         */
        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );


        /*
         * Remove identifier
         *
         * CPT_Welt_WELT
         *
         * becomes:
         *
         * Welt_WELT
         */
        $filename = preg_replace(
            '/^CPT_/',
            '',
            $filename
        );


        /*
         * Split
         *
         * Name_Code
         */
        $parts = explode(
            '_',
            $filename
        );


        if (count($parts) < 2) {
            return [
                'success' => false,
                'message' => $originalName . ' — invalid format',
            ];
        }


        /*
         * Last part = code
         *
         * Everything before = name
         */
        $code = array_pop($parts);

        $name = implode(
            '_',
            $parts
        );


        $name = str_replace(
            '_',
            ' ',
            $name
        );


        $name = trim($name);
        $code = trim($code);


        if ($name === '' || $code === '') {
            return [
                'success' => false,
                'message' => $originalName . ' — missing data',
            ];
        }


        ChestPocketType::updateOrCreate(
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
