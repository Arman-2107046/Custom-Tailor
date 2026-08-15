<?php

namespace App\Services;

use App\Services\BulkUpload\BodiesUploader;
use App\Services\BulkUpload\BodyTypesUploader;
use App\Services\BulkUpload\ButtonImagesUploader;
use App\Services\BulkUpload\ChestPocketTypesUploader;
use App\Services\BulkUpload\FabricsUploader;
use App\Services\BulkUpload\ChestPocketsUploader;
use App\Services\BulkUpload\LapelCategoriesUploader;
use App\Services\BulkUpload\LapelSubcategoriesUploader;
use App\Services\BulkUpload\LapelsUploader;
use App\Services\BulkUpload\SidePocketTypesUploader;
use App\Services\BulkUpload\SidePocketsUploader;
use App\Services\BulkUpload\SleevesUploader;
use App\Services\BulkUpload\BodyButtonsUploader;
use App\Services\BulkUpload\SleeveTypesUploader;
use App\Services\BulkUpload\LiningTypesUploader;
use App\Services\BulkUpload\DefaultLiningsUploader;
use App\Services\BulkUpload\CustomLiningFabricsUploader;
use App\Services\BulkUpload\CustomLiningsUploader;

class BulkUploadService
{
    public function process(array $files): array
    {
        /*
        |--------------------------------------------------------------------------
        | Upload dependency priority
        |--------------------------------------------------------------------------
        */

        $priority = [

            'FAB' => 1,

            'LT' => 2,

            'CF' => 3,

            'CL' => 4,

            'BT' => 5,
            'BD' => 6,

            'DL' => 7,

            'BI' => 8,
            'BB' => 9,

            'SLT' => 10,
            'SL'  => 11,

            'CPT' => 12,
            'CP'  => 13,

            'SPT' => 14,
            'SP'  => 15,

            'LPC' => 16,
            'LPS' => 17,
            'LP'  => 18,
        ];


        /*
        |--------------------------------------------------------------------------
        | Sort uploaded files
        |--------------------------------------------------------------------------
        */

        usort($files, function ($a, $b) use ($priority) {

            $prefixA = explode(
                '_',
                pathinfo($a['name'], PATHINFO_FILENAME)
            )[0];

            $prefixB = explode(
                '_',
                pathinfo($b['name'], PATHINFO_FILENAME)
            )[0];

            return (
                $priority[$prefixA] ?? 999
            ) <=>
            (
                $priority[$prefixB] ?? 999
            );
        });


        /*
        |--------------------------------------------------------------------------
        | Store results
        |--------------------------------------------------------------------------
        */

        $results = [];

        $failed = [];


        /*
        |--------------------------------------------------------------------------
        | Process uploads
        |--------------------------------------------------------------------------
        */

        foreach ($files as $file) {

            $filename = pathinfo(
                $file['name'],
                PATHINFO_FILENAME
            );

            $prefix = explode(
                '_',
                $filename
            )[0];


            /*
            |--------------------------------------------------------------------------
            | Process current file
            |--------------------------------------------------------------------------
            */

            $result = match ($prefix) {

                'FAB' =>
                    app(FabricsUploader::class)
                        ->handle($file),

                'LT' =>
                    app(LiningTypesUploader::class)
                        ->handle($file),

                'CF' =>
                    app(CustomLiningFabricsUploader::class)
                        ->handle($file),

                'CL' =>
                    app(CustomLiningsUploader::class)
                        ->handle($file),

                'BT' =>
                    app(BodyTypesUploader::class)
                        ->handle($file),

                'BD' =>
                    app(BodiesUploader::class)
                        ->handle($file),

                'DL' =>
                    app(DefaultLiningsUploader::class)
                        ->handle($file),

                'BI' =>
                    app(ButtonImagesUploader::class)
                        ->handle($file),

                'BB' =>
                    app(BodyButtonsUploader::class)
                        ->handle($file),

                'SLT' =>
                    app(SleeveTypesUploader::class)
                        ->handle($file),

                'SL' =>
                    app(SleevesUploader::class)
                        ->handle($file),

                'CPT' =>
                    app(ChestPocketTypesUploader::class)
                        ->handle($file),

                'CP' =>
                    app(ChestPocketsUploader::class)
                        ->handle($file),

                'SPT' =>
                    app(SidePocketTypesUploader::class)
                        ->handle($file),

                'SP' =>
                    app(SidePocketsUploader::class)
                        ->handle($file),

                'LPC' =>
                    app(LapelCategoriesUploader::class)
                        ->handle($file),

                'LPS' =>
                    app(LapelSubcategoriesUploader::class)
                        ->handle($file),

                'LP' =>
                    app(LapelsUploader::class)
                        ->handle($file),

                default => [
                    'success' => false,
                    'message' => "Unknown upload prefix: {$prefix}",
                ],
            };


            /*
            |--------------------------------------------------------------------------
            | Save result
            |--------------------------------------------------------------------------
            */

            $results[] = [
                'file' => $file['name'],
                'result' => $result,
            ];


            /*
            |--------------------------------------------------------------------------
            | Track failures
            |--------------------------------------------------------------------------
            */

            if (($result['success'] ?? false) !== true) {

                $failed[] = [
                    'file' => $file['name'],
                    'message' => $result['message'] ?? 'Unknown error',
                    'debug' => $result['debug'] ?? null,
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Return failures
        |--------------------------------------------------------------------------
        */

        if (count($failed) > 0) {

            return [
                'success' => false,

                'message' => count($failed)
                    . ' file(s) failed during processing.',

                'failed' => $failed,

                'results' => $results,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Everything succeeded
        |--------------------------------------------------------------------------
        */

        return [
            'success' => true,

            'message' => 'All images processed successfully.',

            'results' => $results,
        ];
    }
}
