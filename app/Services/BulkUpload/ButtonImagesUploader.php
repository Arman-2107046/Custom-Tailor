<?php

namespace App\Services\BulkUpload;

use App\Models\ButtonImage;


class ButtonImagesUploader
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
         * BI_Button1.png
         *
         * becomes:
         *
         * BI_Button1
         */
        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );



        /*
         * Split filename
         *
         * BI_Button1
         *
         * becomes:
         *
         * BI
         * Button1
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
         * Remove identifier
         */
        $identifier = strtoupper(
            array_shift($parts)
        );



        if ($identifier !== 'BI') {

            return [
                'success' => false,
                'message' => $originalName . ' — invalid identifier',
            ];

        }




        /*
         * Remaining parts are button name
         *
         * Black_Metal
         *
         * becomes:
         *
         * Black Metal
         */
        $name = implode(
            ' ',
            $parts
        );


        $name = trim($name);




        if ($name === '') {

            return [
                'success' => false,
                'message' => $originalName . ' — missing button name',
            ];

        }





        ButtonImage::updateOrCreate(

            [
                'name' => $name,
            ],

            [
                'diagram' => $url,
            ]

        );



        return [

            'success' => true,

            'message' =>
                "{$name} button image imported successfully",

        ];
    }
}
