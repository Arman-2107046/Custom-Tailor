<?php

namespace App\Services\BulkUpload;

use App\Models\CustomLiningFabric;

class CustomLiningFabricsUploader
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
        | CF_Blue Silk.png
        |
        | becomes:
        |
        | CF_Blue Silk
        |
        */

        $filename = pathinfo(
            $originalName,
            PATHINFO_FILENAME
        );

        /*
        |--------------------------------------------------------------------------
        | Remove CF identifier
        |--------------------------------------------------------------------------
        */

        $filename = preg_replace(
            '/^CF_/',
            '',
            $filename
        );

        $name = trim($filename);

        if (! $name) {
            return [
                'success' => false,
                'message' => $originalName . ' — invalid name',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Create / Update Custom Lining Fabric
        |--------------------------------------------------------------------------
        */

        CustomLiningFabric::updateOrCreate(
            [
                'name' => $name,
            ],
            [
                'image' => $url,
                'status' => true,
            ]
        );

        return [
            'success' => true,
            'message' => "{$originalName} imported successfully",
        ];
    }
}
