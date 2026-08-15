<?php

namespace App\Services\BulkUpload;

use App\Models\SidePocketType;

class SidePocketTypesUploader
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
         * Example:
         *
         * SPT_Welt_WELT.png
         *
         * becomes:
         *
         * SPT_Welt_WELT
         */
        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );


        /*
         * Remove identifier
         *
         * SPT_Welt_WELT
         *
         * becomes:
         *
         * Welt_WELT
         */
        $filename = preg_replace(
            '/^SPT_/',
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
         */
        $code = strtoupper(
            trim(array_pop($parts))
        );


        /*
         * Remaining parts = name
         */
        $name = implode(
            '_',
            $parts
        );


        $name = trim(
            str_replace('_', ' ', $name)
        );



        if ($name === '' || $code === '') {

            return [
                'success' => false,
                'message' => $originalName . ' — missing data',
            ];

        }



        SidePocketType::updateOrCreate(
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
