<?php

namespace App\Services\BulkUpload;

use App\Models\Fabric;
use App\Models\Sleeve;
use App\Models\SleeveType;

class SleevesUploader
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
         * SL_ES_Black Stripe.png
         * SL_ES_Black Stripe_1.png
         */


        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );


        /*
         * Remove identifier
         *
         * SL_ES_Black Stripe
         *
         * becomes:
         *
         * ES_Black Stripe
         */


        $filename = preg_replace(
            '/^SL_/',
            '',
            $filename
        );


        $parts = explode('_', $filename);


        /*
         * ES_Fabric
         * ES_Fabric_1
         */


        if (count($parts) < 2 || count($parts) > 3) {

            return [
                'success' => false,
                'message' => $originalName . ' — invalid format',
            ];

        }


        /*
         * First part = Sleeve Type Code
         */

        $sleeveTypeCode = trim($parts[0]);


        /*
         * Second part = Fabric Name
         */

        $fabricName = trim($parts[1]);


        /*
         * Default flag
         */

        $isDefault = false;


        if (isset($parts[2])) {


            if ($parts[2] !== '1') {

                return [
                    'success' => false,
                    'message' => $originalName . ' — invalid default flag',
                ];

            }


            $isDefault = true;
        }



        /*
         * Find Sleeve Type
         */

        $sleeveType = SleeveType::where(
            'code',
            $sleeveTypeCode
        )->first();


        if (! $sleeveType) {

            return [
                'success' => false,
                'message' => "{$sleeveTypeCode} — sleeve type not found",
            ];

        }



        /*
         * Find Fabric
         */

        $fabric = Fabric::where(
            'name',
            $fabricName
        )->first();



        if (! $fabric) {

            return [
                'success' => false,
                'message' => "{$fabricName} — fabric not found",
            ];

        }



        /*
         * Only one default sleeve per fabric
         */

        if ($isDefault) {

            Sleeve::where(
                'fabric_id',
                $fabric->id
            )->update([
                'is_default' => false,
            ]);

        }



        /*
         * Insert / Update
         */

        Sleeve::updateOrCreate(
            [
                'fabric_id' => $fabric->id,
                'sleeve_type_id' => $sleeveType->id,
            ],
            [
                'image' => $url,

                'layer_index' => 150,

                'is_default' => $isDefault,

                'status' => true,
            ]
        );


        return [
            'success' => true,
            'message' => "{$sleeveType->name} imported successfully",
        ];
    }
}
