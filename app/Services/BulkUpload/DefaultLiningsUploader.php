<?php

namespace App\Services\BulkUpload;

use App\Models\BodyType;
use App\Models\Fabric;
use App\Models\LiningType;
use App\Models\DefaultLining;

class DefaultLiningsUploader
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
        | DL_SB1_Default_Black Wool.png
        |
        | becomes:
        |
        | DL_SB1_Default_Black Wool
        |
        */

        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );

        /*
        |--------------------------------------------------------------------------
        | Remove DL identifier
        |--------------------------------------------------------------------------
        */

        $filename = preg_replace(
            '/^DL_/',
            '',
            $filename
        );

        /*
        |--------------------------------------------------------------------------
        | Split filename
        |--------------------------------------------------------------------------
        |
        | SB1_Default_Black Wool
        |
        */

        $parts = explode('_', $filename);

        if (count($parts) < 3) {
            return [
                'success' => false,
                'message' => $originalName . ' — invalid format',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve values
        |--------------------------------------------------------------------------
        */

        $bodyCode = trim($parts[0]);
        $liningTypeName = trim($parts[1]);

        /*
        |--------------------------------------------------------------------------
        | Fabric can contain spaces
        |--------------------------------------------------------------------------
        |
        | Black Wool
        |
        */

        $fabricName = trim(
            implode(
                '_',
                array_slice($parts, 2)
            )
        );

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
                'message' => "{$bodyCode} body type not found",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Find Fabric
        |--------------------------------------------------------------------------
        */

        $fabric = Fabric::where(
            'name',
            str_replace('_', ' ', $fabricName)
        )->first();

        if (! $fabric) {
            return [
                'success' => false,
                'message' => "{$fabricName} fabric not found",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Find Lining Type
        |--------------------------------------------------------------------------
        */

        $liningType = LiningType::where(
            'name',
            $liningTypeName
        )->first();

        if (! $liningType) {
            return [
                'success' => false,
                'message' => "{$liningTypeName} lining type not found",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Create / Update Default Lining
        |--------------------------------------------------------------------------
        |
        | One default lining per fabric + body type combination.
        |
        */

        DefaultLining::updateOrCreate(
            [
                'body_type_id' => $bodyType->id,
                'fabric_id' => $fabric->id,
            ],
            [
                'lining_type_id' => $liningType->id,
                'image' => $url,
                'layer_index' => 0,
                'status' => true,
            ]
        );

        return [
            'success' => true,
            'message' => "{$originalName} imported successfully",
        ];
    }
}
