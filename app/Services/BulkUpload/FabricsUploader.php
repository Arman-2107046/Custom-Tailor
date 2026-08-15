<?php

namespace App\Services\BulkUpload;

use App\Models\Fabric;

class FabricsUploader
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
        | FAB_120_Blue Stripe_1.png
        | FAB_120_Blue Stripe.png
        |
        | becomes:
        |
        | FAB_120_Blue Stripe_1
        | FAB_120_Blue Stripe
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
        | FAB_120_Blue Stripe_1
        |
        | parts:
        |
        | [0] => FAB
        | [1] => 120
        | [2] => Blue Stripe
        | [3] => 1
        |
        */

        $parts = explode('_', $filename);

        /*
        |--------------------------------------------------------------------------
        | Expected:
        |
        | FAB_Price_Name
        | FAB_Price_Name_1
        |--------------------------------------------------------------------------
        */

        if (count($parts) < 3 || count($parts) > 4) {
            return [
                'success' => false,
                'message' =>
                    "{$originalName} — invalid format. Expected: FAB_Price_FabricName[_1]",
                'debug' => [
                    'filename' => $filename,
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

        if ($identifier !== 'FAB') {
            return [
                'success' => false,
                'message' =>
                    "{$originalName} — invalid identifier. Expected FAB",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Price
        |--------------------------------------------------------------------------
        */

        $price = trim($parts[1]);

        if (! is_numeric($price)) {
            return [
                'success' => false,
                'message' =>
                    "{$originalName} — invalid price: {$price}",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Fabric Name
        |--------------------------------------------------------------------------
        |
        | This allows fabric names containing underscores as well.
        |
        | Example:
        |
        | FAB_120_Blue_Stripe_1
        |
        | becomes:
        |
        | Blue Stripe
        |--------------------------------------------------------------------------
        */

        $nameParts = array_slice($parts, 2);

        /*
        |--------------------------------------------------------------------------
        | Default flag
        |--------------------------------------------------------------------------
        */

        $isDefault = false;

        if (end($nameParts) === '1') {

            $isDefault = true;

            array_pop($nameParts);
        }

        /*
        |--------------------------------------------------------------------------
        | Build fabric name
        |--------------------------------------------------------------------------
        */

        $name = trim(
            implode(' ', $nameParts)
        );

        if ($name === '') {
            return [
                'success' => false,
                'message' =>
                    "{$originalName} — fabric name is missing",
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        $status = true;

        /*
        |--------------------------------------------------------------------------
        | If this fabric is default,
        | remove previous default
        |--------------------------------------------------------------------------
        */

        if ($isDefault) {

            Fabric::where(
                'is_default',
                true
            )->update([
                'is_default' => false,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create / Update Fabric
        |--------------------------------------------------------------------------
        */

        $fabric = Fabric::updateOrCreate(
            [
                'name' => $name,
            ],
            [
                'price' => (float) $price,
                'image' => $url,
                'is_default' => $isDefault,
                'status' => $status,
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
                "{$name} imported successfully",
            'debug' => [
                'filename' => $originalName,
                'name' => $name,
                'price' => (float) $price,
                'is_default' => $isDefault,
                'fabric_id' => $fabric->id,
            ],
        ];
    }
}
