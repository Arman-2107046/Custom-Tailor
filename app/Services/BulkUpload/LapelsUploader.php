<?php

namespace App\Services\BulkUpload;

use App\Models\Body;
use App\Models\BodyType;
use App\Models\Fabric;
use App\Models\Lapel;
use App\Models\LapelCategory;
use App\Models\LapelSubCategory;

class LapelsUploader
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
         * LP_SB1_Notch_Large_Blue Stripe_1.png
         *
         * becomes:
         *
         * SB1_Notch_Large_Blue Stripe_1
         */

        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );


        /*
         * Remove LP identifier
         */
        $filename = preg_replace(
            '/^LP_/',
            '',
            $filename
        );


        $parts = explode('_', $filename);


        /*
         * Normal:
         * SB1_Notch_Large_Blue Stripe
         *
         * Default:
         * SB1_Notch_Large_Blue Stripe_1
         */

        if (count($parts) < 4 || count($parts) > 5) {

            return [
                'success' => false,
                'message' => $originalName . ' — invalid format',
            ];

        }


        $bodyCode = trim($parts[0]);

        $categoryName = trim($parts[1]);

        $subCategoryName = trim($parts[2]);


        /*
         * Default flag
         */
        $isDefault = false;


        if (count($parts) === 5) {

            if ($parts[4] !== '1') {

                return [
                    'success' => false,
                    'message' => $originalName . ' — invalid default flag',
                ];

            }

            $isDefault = true;
        }


        /*
         * Fabric is always before default suffix
         */
        $fabricName = trim($parts[3]);



        /*
         * Find Body Type
         */
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
         * Find Body
         */
        $body = Body::where('body_type_id', $bodyType->id)
            ->where('fabric_id', $fabric->id)
            ->first();


        if (! $body) {

            return [
                'success' => false,
                'message' =>
                    "{$bodyCode} + {$fabricName} body not found",
            ];

        }



        /*
         * Find Category
         */
        $category = LapelCategory::where(
            'name',
            $categoryName
        )->first();


        if (! $category) {

            return [
                'success' => false,
                'message' =>
                    "{$categoryName} category not found",
            ];

        }



        /*
         * Find Sub Category
         */
        $subCategory = LapelSubCategory::where(
            'name',
            $subCategoryName
        )->first();


        if (! $subCategory) {

            return [
                'success' => false,
                'message' =>
                    "{$subCategoryName} subcategory not found",
            ];

        }



        /*
         * Remove previous default
         */
        if ($isDefault) {

            Lapel::where('body_id', $body->id)
                ->where('fabric_id', $fabric->id)
                ->update([
                    'is_default' => false,
                ]);

        }



        /*
         * Create / Update
         */
        Lapel::updateOrCreate(
            [
                'body_id' => $body->id,
                'fabric_id' => $fabric->id,
                'lapel_category_id' => $category->id,
                'lapel_subcategory_id' => $subCategory->id,
            ],
            [
                'image' => $url,
                'is_default' => $isDefault,
                'layer_index' => 150,
                'status' => true,
            ]
        );


        return [
            'success' => true,
            'message' => "{$originalName} imported successfully",
        ];
    }
}
