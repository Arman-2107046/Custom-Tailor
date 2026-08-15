<?php

namespace App\Services\BulkUpload;

use App\Models\Body;
use App\Models\BodyType;
use App\Models\Fabric;


class BodiesUploader
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
         * BD_SB1_Black_1.png
         *
         * becomes:
         *
         * BD_SB1_Black_1
         */
        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );



        /*
         * Split filename
         *
         * BD_BodyCode_Fabric_Default
         */
        $parts = explode(
            '_',
            $filename
        );



        if (
            count($parts) < 3 ||
            count($parts) > 4
        ) {

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



        if ($identifier !== 'BD') {

            return [
                'success' => false,
                'message' => $originalName . ' — invalid identifier',
            ];

        }




        /*
         * Remaining:
         *
         * SB1_Black
         *
         * SB1_Black_1
         */

        $bodyCode = strtoupper(
            trim($parts[0])
        );


        $fabricName = trim(
            $parts[1]
        );



        /*
         * _1 means default
         */
        $isDefault =
            isset($parts[2])
            &&
            $parts[2] === '1';




        $bodyType = BodyType::where(
            'code',
            $bodyCode
        )->first();



        if (! $bodyType) {

            return [
                'success' => false,
                'message' => "{$bodyCode} body type not found",
            ];

        }





        $fabric = Fabric::where(
            'name',
            $fabricName
        )->first();



        if (! $fabric) {

            return [
                'success' => false,
                'message' => "{$fabricName} fabric not found",
            ];

        }




        if ($isDefault) {

            Body::where(
                'fabric_id',
                $fabric->id
            )
            ->update([
                'is_default' => false,
            ]);

        }




        Body::updateOrCreate(

            [
                'fabric_id' => $fabric->id,

                'body_type_id' => $bodyType->id,
            ],

            [

                'image' => $url,

                'layer_index' => 100,

                'is_default' => $isDefault,

                'status' => true,

            ]

        );



        return [

            'success' => true,

            'message' =>
                "{$bodyCode} {$fabricName} body imported successfully",

        ];
    }
}
