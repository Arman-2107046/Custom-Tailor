<?php

namespace App\Services\BulkUpload;

use App\Models\BodyType;


class BodyTypesUploader
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
         * BT__Single_Breasted_SB1.png
         *
         * becomes:
         *
         * BT__Single_Breasted_SB1
         */
        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );



        /*
         * Remove identifier
         *
         * BT__Single_Breasted_SB1
         *
         * becomes:
         *
         * Single_Breasted_SB1
         */
        $filename = preg_replace(
            '/^BT_/',
            '',
            $filename
        );



        /*
         * Split filename
         *
         * Name_Code
         *
         * Example:
         *
         * Single_Breasted_SB1
         *
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
         * SB1
         */
        $code = array_pop($parts);



        /*
         * Remaining parts = name
         *
         * Single_Breasted
         *
         * becomes:
         *
         * Single Breasted
         */
        $name = implode(
            ' ',
            $parts
        );



        $name = trim($name);
        $code = trim($code);



        if (
            $name === '' ||
            $code === ''
        ) {

            return [
                'success' => false,
                'message' => $originalName . ' — missing name/code',
            ];

        }



        BodyType::updateOrCreate(

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
            'message' => "{$name} ({$code}) imported successfully",
        ];
    }
}
