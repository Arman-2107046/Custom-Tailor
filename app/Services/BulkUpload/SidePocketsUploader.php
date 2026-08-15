<?php

namespace App\Services\BulkUpload;

use App\Models\Fabric;
use App\Models\SidePocket;
use App\Models\SidePocketType;

class SidePocketsUploader
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
         * SP_WELT_Black_1.png
         *
         * becomes:
         *
         * SP_WELT_Black_1
         */
        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );


        /*
         * Remove identifier
         *
         * SP_WELT_Black_1
         *
         * becomes:
         *
         * WELT_Black_1
         */
        $filename = preg_replace(
            '/^SP_+/',
            '',
            $filename
        );


        /*
         * Split filename
         *
         * WELT_Black
         * WELT_Black_1
         */
        $parts = explode(
            '_',
            $filename
        );


        if (count($parts) < 2 || count($parts) > 3) {

            return [
                'success' => false,
                'message' => $originalName . ' — invalid format',
            ];

        }



        /*
         * First part:
         *
         * Side Pocket Type Code
         */
        $typeCode = strtoupper(
            trim($parts[0])
        );


        /*
         * Second part:
         *
         * Fabric Name
         */
        $fabricName = trim($parts[1]);



        /*
         * Default flag
         */
        $isDefault = false;


        if (count($parts) === 3) {

            if ($parts[2] !== '1') {

                return [
                    'success' => false,
                    'message' => $originalName . ' — invalid default flag',
                ];

            }

            $isDefault = true;
        }



        /*
         * Find Side Pocket Type
         */
        $sidePocketType = SidePocketType::where(
            'code',
            $typeCode
        )->first();



        if (! $sidePocketType) {

            return [
                'success' => false,
                'message' => "{$typeCode} side pocket type not found",
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
                'message' => "{$fabricName} fabric not found",
            ];

        }



        /*
         * Remove previous default
         * for same fabric
         */
        if ($isDefault) {

            SidePocket::where(
                'fabric_id',
                $fabric->id
            )->update([
                'is_default' => false,
            ]);

        }



        /*
         * Create / Update
         */
        SidePocket::updateOrCreate(
            [
                'fabric_id' => $fabric->id,
                'side_pocket_type_id' => $sidePocketType->id,
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
            'message' => "{$typeCode} {$fabricName} imported successfully",
        ];
    }
}
