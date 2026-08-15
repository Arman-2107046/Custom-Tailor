<?php

namespace App\Services\BulkUpload;

use App\Models\ChestPocket;
use App\Models\ChestPocketType;
use App\Models\Fabric;
use Throwable;

class ChestPocketsUploader
{
    public function handle(array $file): array
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | STEP 1: Receive file data
            |--------------------------------------------------------------------------
            */

            $originalName = $file['name'] ?? null;
            $url = $file['url'] ?? null;

            if (! $originalName) {
                return [
                    'success' => false,
                    'message' => 'STEP 1 FAILED: File name is missing.',
                    'debug' => [
                        'received_data' => $file,
                    ],
                ];
            }

            if (! $url) {
                return [
                    'success' => false,
                    'message' => "STEP 1 FAILED: Cloudinary URL is missing for {$originalName}.",
                    'debug' => [
                        'file_name' => $originalName,
                        'received_data' => $file,
                    ],
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 2: Remove extension
            |--------------------------------------------------------------------------
            |
            | CP_NP_Blue Stripe.png
            |
            | becomes:
            |
            | CP_NP_Blue Stripe
            |
            */

            $filename = pathinfo(
                $originalName,
                PATHINFO_FILENAME
            );

            if (! $filename) {
                return [
                    'success' => false,
                    'message' => "STEP 2 FAILED: Could not extract filename from {$originalName}.",
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 3: Validate CP_ prefix
            |--------------------------------------------------------------------------
            */

            if (! str_starts_with(
                strtoupper($filename),
                'CP_'
            )) {
                return [
                    'success' => false,
                    'message' => "STEP 3 FAILED: {$originalName} must start with CP_.",
                    'debug' => [
                        'filename' => $filename,
                        'expected_format' => 'CP_NP_Blue Stripe.png',
                    ],
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 4: Remove CP_
            |--------------------------------------------------------------------------
            |
            | CP_NP_Blue Stripe
            |
            | becomes:
            |
            | NP_Blue Stripe
            |
            */

            $filename = preg_replace(
                '/^CP_/i',
                '',
                $filename
            );

            if (! $filename) {
                return [
                    'success' => false,
                    'message' => "STEP 4 FAILED: Nothing remains after removing CP_ from {$originalName}.",
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 5: Detect _1 default suffix
            |--------------------------------------------------------------------------
            |
            | CP_NP_Blue Stripe_1.png
            |
            | means:
            |
            | is_default = true
            |
            */

            $isDefault = false;

            if (preg_match('/_1$/', $filename)) {

                $isDefault = true;

                $filename = preg_replace(
                    '/_1$/',
                    '',
                    $filename
                );
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 6: Split type code and fabric name
            |--------------------------------------------------------------------------
            */

            $parts = explode(
                '_',
                $filename
            );

            if (count($parts) !== 2) {
                return [
                    'success' => false,
                    'message' => "STEP 6 FAILED: Invalid filename format for {$originalName}.",
                    'debug' => [
                        'filename_after_processing' => $filename,
                        'parts' => $parts,
                        'expected' => 'CP_NP_Blue Stripe.png',
                    ],
                ];
            }

            $typeCode = strtoupper(
                trim($parts[0])
            );

            $fabricName = trim(
                $parts[1]
            );


            if (! $typeCode) {
                return [
                    'success' => false,
                    'message' => "STEP 6 FAILED: Chest pocket type code is empty.",
                    'debug' => [
                        'filename' => $originalName,
                        'parts' => $parts,
                    ],
                ];
            }

            if (! $fabricName) {
                return [
                    'success' => false,
                    'message' => "STEP 6 FAILED: Fabric name is empty.",
                    'debug' => [
                        'filename' => $originalName,
                        'parts' => $parts,
                    ],
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 7: Find Chest Pocket Type
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | NP → Normal Pocket
            |
            */

            $chestPocketType = ChestPocketType::where(
                'code',
                $typeCode
            )->first();

            if (! $chestPocketType) {

                return [
                    'success' => false,
                    'message' => "STEP 7 FAILED: Chest pocket type not found.",
                    'debug' => [
                        'filename' => $originalName,
                        'type_code' => $typeCode,
                        'fabric_name' => $fabricName,
                        'is_default' => $isDefault,
                        'searched_column' => 'chest_pocket_types.code',
                    ],
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 8: Find Fabric
            |--------------------------------------------------------------------------
            */

            $fabric = Fabric::where(
                'name',
                $fabricName
            )->first();

            if (! $fabric) {

                return [
                    'success' => false,
                    'message' => "STEP 8 FAILED: Fabric not found.",
                    'debug' => [
                        'filename' => $originalName,
                        'type_code' => $typeCode,
                        'chest_pocket_type_id' => $chestPocketType->id,
                        'chest_pocket_type_name' => $chestPocketType->name,
                        'fabric_name_searched' => $fabricName,
                        'is_default' => $isDefault,
                        'searched_column' => 'fabrics.name',
                    ],
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 9: Remove previous default
            |--------------------------------------------------------------------------
            */

            if ($isDefault) {

                ChestPocket::where(
                    'fabric_id',
                    $fabric->id
                )->update([
                    'is_default' => false,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 10: Create / Update Chest Pocket
            |--------------------------------------------------------------------------
            */

            $chestPocket = ChestPocket::updateOrCreate(
                [
                    'fabric_id' => $fabric->id,
                    'chest_pocket_type_id' => $chestPocketType->id,
                ],
                [
                    'image' => $url,
                    'layer_index' => 100,
                    'is_default' => $isDefault,
                    'status' => true,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | STEP 11: Verify database record
            |--------------------------------------------------------------------------
            */

            if (! $chestPocket->exists) {

                return [
                    'success' => false,
                    'message' => "STEP 11 FAILED: Chest pocket record was not created.",
                    'debug' => [
                        'filename' => $originalName,
                        'fabric_id' => $fabric->id,
                        'fabric_name' => $fabric->name,
                        'chest_pocket_type_id' => $chestPocketType->id,
                        'chest_pocket_type_name' => $chestPocketType->name,
                    ],
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            return [
                'success' => true,
                'message' => "SUCCESS: {$originalName} imported successfully.",
                'debug' => [
                    'filename' => $originalName,

                    'type_code' => $typeCode,

                    'chest_pocket_type' => [
                        'id' => $chestPocketType->id,
                        'name' => $chestPocketType->name,
                        'code' => $chestPocketType->code,
                    ],

                    'fabric' => [
                        'id' => $fabric->id,
                        'name' => $fabric->name,
                    ],

                    'is_default' => $isDefault,

                    'chest_pocket' => [
                        'id' => $chestPocket->id,
                        'image' => $chestPocket->image,
                        'layer_index' => $chestPocket->layer_index,
                        'is_default' => $chestPocket->is_default,
                        'status' => $chestPocket->status,
                    ],
                ],
            ];

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | UNEXPECTED ERROR
            |--------------------------------------------------------------------------
            */

            return [
                'success' => false,
                'message' => 'UNEXPECTED ERROR: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'exception' => get_class($e),
                ],
            ];
        }
    }
}





// namespace App\Services\BulkUpload;

// use App\Models\ChestPocket;
// use App\Models\ChestPocketType;
// use App\Models\Fabric;

// class ChestPocketsUploader
// {
//     public function handle(array $file): array
//     {
//         $originalName = $file['name'] ?? null;
//         $url = $file['url'] ?? null;


//         if (! $originalName || ! $url) {
//             return [
//                 'success' => false,
//                 'message' => 'Invalid file data',
//             ];
//         }


//         /*
//          * Remove extension
//          *
//          * CP_WELT_Black_1.png
//          *
//          * becomes:
//          *
//          * CP_WELT_Black_1
//          */
//         $filename = pathinfo(
//             $originalName,
//             PATHINFO_FILENAME
//         );


//         /*
//          * Remove identifier
//          *
//          * CP_WELT_Black_1
//          *
//          * becomes:
//          *
//          * WELT_Black_1
//          */
//         $filename = preg_replace(
//             '/^CP_/',
//             '',
//             $filename
//         );


//         /*
//          * Split filename
//          *
//          * WELT_Black
//          * WELT_Black_1
//          */
//         $parts = explode(
//             '_',
//             $filename
//         );


//         if (count($parts) < 2 || count($parts) > 3) {
//             return [
//                 'success' => false,
//                 'message' => $originalName . ' — invalid format',
//             ];
//         }


//         /*
//          * First part = Chest Pocket Type Code
//          */
//         $typeCode = strtoupper(
//             trim($parts[0])
//         );


//         /*
//          * Second part = Fabric Name
//          */
//         $fabricName = trim($parts[1]);


//         /*
//          * _1 = default
//          */
//         $isDefault = isset($parts[2])
//             && $parts[2] === '1';



//         /*
//          * Find chest pocket type
//          */
//         $chestPocketType = ChestPocketType::where(
//             'code',
//             $typeCode
//         )->first();


//         if (! $chestPocketType) {
//             return [
//                 'success' => false,
//                 'message' => "{$typeCode} — chest pocket type not found",
//             ];
//         }



//         /*
//          * Find fabric
//          */
//         $fabric = Fabric::where(
//             'name',
//             $fabricName
//         )->first();


//         if (! $fabric) {
//             return [
//                 'success' => false,
//                 'message' => "{$fabricName} — fabric not found",
//             ];
//         }



//         /*
//          * Only one default per fabric
//          */
//         if ($isDefault) {

//             ChestPocket::where(
//                 'fabric_id',
//                 $fabric->id
//             )->update([
//                 'is_default' => false,
//             ]);

//         }



//         /*
//          * Create / update
//          */
//         ChestPocket::updateOrCreate(
//             [
//                 'fabric_id' => $fabric->id,
//                 'chest_pocket_type_id' => $chestPocketType->id,
//             ],
//             [
//                 'image' => $url,
//                 'layer_index' => 100,
//                 'is_default' => $isDefault,
//                 'status' => true,
//             ]
//         );


//         return [
//             'success' => true,
//             'message' => "{$typeCode} {$fabricName} imported successfully",
//         ];
//     }
// }
