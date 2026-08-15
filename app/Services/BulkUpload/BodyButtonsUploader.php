<?php

namespace App\Services\BulkUpload;

use App\Models\BodyType;
use App\Models\ButtonImage;
use App\Models\BodyButton;

class BodyButtonsUploader
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
        |--------------------------------------------------------------------------
        | Remove extension
        |--------------------------------------------------------------------------
        |
        | BB_SB1_Button1.png
        |
        | becomes:
        |
        | BB_SB1_Button1
        |
        */

        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );

        /*
        |--------------------------------------------------------------------------
        | Split filename
        |--------------------------------------------------------------------------
        |
        | BB_SB1_Button1
        |
        | becomes:
        |
        | [
        |     BB,
        |     SB1,
        |     Button1
        | ]
        |
        */

        $parts = explode('_', $filename);

        /*
        |--------------------------------------------------------------------------
        | Expected format
        |--------------------------------------------------------------------------
        |
        | BB_SB1_Button1.png
        | BB_SB1_Button1_1.png
        |
        */

        if (count($parts) < 3 || count($parts) > 4) {
            return [
                'success' => false,
                'message' =>
                    "{$originalName} — invalid format. Expected: BB_BodyTypeCode_ButtonName[_1]",
                'debug' => [
                    'filename' => $originalName,
                    'parts' => $parts,
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Identifier
        |--------------------------------------------------------------------------
        */

        $identifier = strtoupper(
            trim($parts[0])
        );

        if ($identifier !== 'BB') {
            return [
                'success' => false,
                'message' =>
                    "{$originalName} — invalid identifier. Expected BB",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Body Type Code
        |--------------------------------------------------------------------------
        |
        | SB1
        | DB2
        | etc.
        |
        */

        $bodyCode = strtoupper(
            trim($parts[1])
        );

        /*
        |--------------------------------------------------------------------------
        | Button Image Name
        |--------------------------------------------------------------------------
        */

        $buttonName = trim(
            $parts[2]
        );

        /*
        |--------------------------------------------------------------------------
        | Default Flag
        |--------------------------------------------------------------------------
        |
        | BB_SB1_Button1.png
        |       → not default
        |
        | BB_SB1_Button1_1.png
        |       → default
        |
        */

        $isDefault = false;

        if (isset($parts[3])) {

            if ($parts[3] !== '1') {
                return [
                    'success' => false,
                    'message' =>
                        "{$originalName} — invalid default flag. Only _1 is allowed.",
                ];
            }

            $isDefault = true;
        }

        /*
        |--------------------------------------------------------------------------
        | Find Body Type
        |--------------------------------------------------------------------------
        */

        $bodyType = BodyType::where(
            'code',
            $bodyCode
        )->first();

        if (! $bodyType) {
            return [
                'success' => false,
                'message' =>
                    "{$bodyCode} body type not found",
                'debug' => [
                    'filename' => $originalName,
                    'body_code_searched' => $bodyCode,
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Find Button Image
        |--------------------------------------------------------------------------
        */

        $buttonImage = ButtonImage::where(
            'name',
            $buttonName
        )->first();

        if (! $buttonImage) {
            return [
                'success' => false,
                'message' =>
                    "{$buttonName} button image not found",
                'debug' => [
                    'filename' => $originalName,
                    'button_name_searched' => $buttonName,
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | If this is default
        |--------------------------------------------------------------------------
        |
        | Only one default button per body type.
        |
        */

        if ($isDefault) {

            BodyButton::where(
                'body_type_id',
                $bodyType->id
            )->update([
                'is_default' => false,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create / Update
        |--------------------------------------------------------------------------
        */

        $bodyButton = BodyButton::updateOrCreate(
            [
                'body_type_id' => $bodyType->id,
                'button_image_id' => $buttonImage->id,
            ],
            [
                'image' => $url,
                'layer_index' => 160,
                'is_default' => $isDefault,
                'status' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        return [
            'success' => true,

            'message' =>
                "{$originalName} imported successfully.",

            'debug' => [
                'filename' => $originalName,

                'body_type' => [
                    'id' => $bodyType->id,
                    'name' => $bodyType->name,
                    'code' => $bodyType->code,
                ],

                'button_image' => [
                    'id' => $buttonImage->id,
                    'name' => $buttonImage->name,
                ],

                'body_button' => [
                    'id' => $bodyButton->id,
                    'image' => $bodyButton->image,
                    'layer_index' => $bodyButton->layer_index,
                    'is_default' => $bodyButton->is_default,
                    'status' => $bodyButton->status,
                ],
            ],
        ];
    }
}
