<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fabric;
use Illuminate\Http\JsonResponse;

class SuitConfiguratorController extends Controller
{
    public function index(): JsonResponse
    {
        $fabrics = Fabric::with([
            /*
            |--------------------------------------------------------------------------
            | Fabric-level options
            |--------------------------------------------------------------------------
            */
            'sleeves.sleeveType',
            'sidePockets.sidePocketType',
            'chestPockets.chestPocketType',

            /*
            |--------------------------------------------------------------------------
            | Custom Linings
            |--------------------------------------------------------------------------
            */
            'customLinings.customLiningFabric',
            'customLinings.liningType',

            /*
            |--------------------------------------------------------------------------
            | Default Linings
            |--------------------------------------------------------------------------
            |
            | Default lining is related to:
            | fabric_id + body_type_id
            |
            */
            'defaultLinings.liningType',
            'defaultLinings.bodyType',

            /*
            |--------------------------------------------------------------------------
            | Bodies
            |--------------------------------------------------------------------------
            */
            'body.bodyType',

            /*
            |--------------------------------------------------------------------------
            | Body-level options
            |--------------------------------------------------------------------------
            */
            'body.lapels.lapelCategory',
            'body.lapels.lapelSubcategory',

            /*
            |--------------------------------------------------------------------------
            | Body Type-level options
            |--------------------------------------------------------------------------
            */
            'body.bodyType.bodyButtons.buttonImage',
        ])
            ->where('status', true)
            ->get();

        return response()->json([
            'success' => true,

            'data' => $fabrics->map(function ($fabric) {

                return [

                    /*
                    |--------------------------------------------------------------------------
                    | Fabric
                    |--------------------------------------------------------------------------
                    */

                    'id' => $fabric->id,
                    'name' => $fabric->name,
                    'price' => $fabric->price,
                    'image' => $fabric->image,
                    'is_default' => $fabric->is_default,


                    /*
                    |--------------------------------------------------------------------------
                    | Sleeves
                    |--------------------------------------------------------------------------
                    */

                    'sleeves' => $fabric->sleeves
                        ->where('status', true)
                        ->values()
                        ->map(function ($sleeve) {

                            return [
                                'id' => $sleeve->id,
                                'image' => $sleeve->image,
                                'layer_index' => $sleeve->layer_index,
                                'is_default' => $sleeve->is_default,

                                'type' => $sleeve->sleeveType
                                    ? [
                                        'id' => $sleeve->sleeveType->id,
                                        'name' => $sleeve->sleeveType->name,
                                        'code' => $sleeve->sleeveType->code,
                                        'diagram' => $sleeve->sleeveType->diagram,
                                    ]
                                    : null,
                            ];
                        }),


                    /*
                    |--------------------------------------------------------------------------
                    | Side Pockets
                    |--------------------------------------------------------------------------
                    */

                    'side_pockets' => $fabric->sidePockets
                        ->where('status', true)
                        ->values()
                        ->map(function ($pocket) {

                            return [
                                'id' => $pocket->id,
                                'image' => $pocket->image,
                                'layer_index' => $pocket->layer_index,
                                'is_default' => $pocket->is_default,

                                'type' => $pocket->sidePocketType
                                    ? [
                                        'id' => $pocket->sidePocketType->id,
                                        'name' => $pocket->sidePocketType->name,
                                        'code' => $pocket->sidePocketType->code,
                                        'diagram' => $pocket->sidePocketType->diagram,
                                    ]
                                    : null,
                            ];
                        }),


                    /*
                    |--------------------------------------------------------------------------
                    | Chest Pockets
                    |--------------------------------------------------------------------------
                    */

                    'chest_pockets' => $fabric->chestPockets
                        ->where('status', true)
                        ->values()
                        ->map(function ($pocket) {

                            return [
                                'id' => $pocket->id,
                                'image' => $pocket->image,
                                'layer_index' => $pocket->layer_index,
                                'is_default' => $pocket->is_default,

                                'type' => $pocket->chestPocketType
                                    ? [
                                        'id' => $pocket->chestPocketType->id,
                                        'name' => $pocket->chestPocketType->name,
                                        'code' => $pocket->chestPocketType->code,
                                        'diagram' => $pocket->chestPocketType->diagram,
                                    ]
                                    : null,
                            ];
                        }),


                    /*
                    |--------------------------------------------------------------------------
                    | Custom Linings
                    |--------------------------------------------------------------------------
                    */

                    'custom_linings' => $fabric->customLinings
                        ->where('status', true)
                        ->values()
                        ->map(function ($lining) {

                            return [
                                'id' => $lining->id,

                                'image' => $lining->image,

                                'layer_index' => $lining->layer_index,

                                'is_default' => $lining->is_default,

                                /*
                                |--------------------------------------------------------------------------
                                | Custom Lining Fabric
                                |--------------------------------------------------------------------------
                                */

                                'fabric' => $lining->customLiningFabric
                                    ? [
                                        'id' => $lining->customLiningFabric->id,
                                        'name' => $lining->customLiningFabric->name,
                                        'image' => $lining->customLiningFabric->image,
                                    ]
                                    : null,

                                /*
                                |--------------------------------------------------------------------------
                                | Lining Type
                                |--------------------------------------------------------------------------
                                */

                                'type' => $lining->liningType
                                    ? [
                                        'id' => $lining->liningType->id,
                                        'name' => $lining->liningType->name,
                                        'code' => $lining->liningType->code,
                                        'diagram' => $lining->liningType->diagram,
                                    ]
                                    : null,
                            ];
                        }),


                    /*
                    |--------------------------------------------------------------------------
                    | Bodies
                    |--------------------------------------------------------------------------
                    */

                    'bodies' => $fabric->body
                        ->where('status', true)
                        ->values()
                        ->map(function ($body) use ($fabric) {

                            $bodyType = $body->bodyType;

                            /*
                            |--------------------------------------------------------------------------
                            | Default Linings
                            |--------------------------------------------------------------------------
                            |
                            | Default lining is determined by:
                            |
                            | fabric_id + body_type_id
                            |
                            */

                            $defaultLinings = $fabric->defaultLinings
                                ->where('body_type_id', $body->body_type_id)
                                ->where('status', true)
                                ->values();

                            return [

                                /*
                                |--------------------------------------------------------------------------
                                | Body
                                |--------------------------------------------------------------------------
                                */

                                'id' => $body->id,

                                'fabric_id' => $body->fabric_id,

                                'body_type_id' => $body->body_type_id,

                                'image' => $body->image,

                                'layer_index' => $body->layer_index,

                                'is_default' => $body->is_default,


                                /*
                                |--------------------------------------------------------------------------
                                | Body Type
                                |--------------------------------------------------------------------------
                                */

                                'body_type' => $bodyType
                                    ? [

                                        'id' => $bodyType->id,

                                        'name' => $bodyType->name,

                                        'code' => $bodyType->code,

                                        'diagram' => $bodyType->diagram,


                                        /*
                                        |--------------------------------------------------------------------------
                                        | Body Buttons
                                        |--------------------------------------------------------------------------
                                        */

                                        'body_buttons' => $bodyType->bodyButtons
                                            ->where('status', true)
                                            ->values()
                                            ->map(function ($button) {

                                                return [

                                                    'id' => $button->id,

                                                    'body_type_id' =>
                                                    $button->body_type_id,

                                                    'button_image_id' =>
                                                    $button->button_image_id,

                                                    'image' =>
                                                    $button->image,

                                                    'layer_index' =>
                                                    $button->layer_index,

                                                    'is_default' =>
                                                    $button->is_default,


                                                    /*
                                                    |--------------------------------------------------------------------------
                                                    | Button Image
                                                    |--------------------------------------------------------------------------
                                                    */

                                                    'button_image' =>
                                                    $button->buttonImage
                                                        ? [

                                                            'id' =>
                                                            $button->buttonImage->id,

                                                            'name' =>
                                                            $button->buttonImage->name,

                                                            'diagram' =>
                                                            $button->buttonImage->diagram,

                                                        ]
                                                        : null,
                                                ];
                                            }),

                                    ]
                                    : null,


                                /*
                                |--------------------------------------------------------------------------
                                | Default Linings
                                |--------------------------------------------------------------------------
                                */

                                'default_linings' => $defaultLinings
                                    ->map(function ($lining) {

                                        return [

                                            'id' => $lining->id,

                                            'fabric_id' =>
                                            $lining->fabric_id,

                                            'body_type_id' =>
                                            $lining->body_type_id,

                                            'lining_type_id' =>
                                            $lining->lining_type_id,

                                            'image' =>
                                            $lining->image,

                                            'layer_index' =>
                                            $lining->layer_index,

                                            'status' =>
                                            $lining->status,


                                            /*
                                            |--------------------------------------------------------------------------
                                            | Lining Type
                                            |--------------------------------------------------------------------------
                                            */

                                            'type' => $lining->liningType
                                                ? [

                                                    'id' =>
                                                    $lining->liningType->id,

                                                    'name' =>
                                                    $lining->liningType->name,

                                                    'code' =>
                                                    $lining->liningType->code,

                                                    'diagram' =>
                                                    $lining->liningType->diagram,

                                                ]
                                                : null,
                                        ];
                                    }),


                                /*
|--------------------------------------------------------------------------
| Lapels
|--------------------------------------------------------------------------
*/

                                'lapels' => $body->lapels
                                    ->where('status', true)
                                    ->values()
                                    ->map(function ($lapel) {

                                        return [

                                            'id' => $lapel->id,

                                            'image' => $lapel->image,

                                            'layer_index' => $lapel->layer_index,

                                            'is_default' => $lapel->is_default,


                                            /*
            |--------------------------------------------------------------------------
            | Lapel Category
            |--------------------------------------------------------------------------
            */

                                            'category' => $lapel->lapelCategory
                                                ? [

                                                    'id' => $lapel->lapelCategory->id,

                                                    'name' => $lapel->lapelCategory->name,

                                                    'diagram' => $lapel->lapelCategory->diagram,

                                                    'is_default' => $lapel->lapelCategory->is_default,

                                                ]
                                                : null,


                                            /*
            |--------------------------------------------------------------------------
            | Lapel Subcategory
            |--------------------------------------------------------------------------
            */

                                            'subcategory' => $lapel->lapelSubcategory
                                                ? [

                                                    'id' => $lapel->lapelSubcategory->id,

                                                    'name' => $lapel->lapelSubcategory->name,

                                                    'diagram' => $lapel->lapelSubcategory->diagram,

                                                    'is_default' => $lapel->lapelSubcategory->is_default,

                                                ]
                                                : null,
                                        ];
                                    }),




                            ];
                        }),
                ];
            }),
        ]);
    }
}
