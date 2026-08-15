<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Fabric;

class SuitConfiguratorController extends Controller
{
    public function index()
    {
        $fabrics = Fabric::with([
            'bodies.sleeves',
            'bodies.bodyButtons',
            'bodies.linings',
            'bodies.sidePockets.sidePocketButtons',
            'bodies.chestPockets.chestPocketButtons',
            'bodies.lapels.subcategories',
        ])->get();

        $fabrics = $fabrics->map(callback: function ($fabric) {
            return [
                'id' => $fabric->id,
                'fabric_name' => $fabric->fabric_name,
                'image' => $this->publicImage($fabric->image),

                'bodies' => $fabric->bodies->map(function ($body) {
                    return [
                        'id' => $body->id,
                        'body_name' => $body->body_name,
                        'image' => $this->publicImage($body->image),
                        'body_diagram' => $this->publicImage($body->body_diagram),

                        'sleeves' => $body->sleeves->map(fn ($s) => [
                            'id' => $s->id,
                            'sleeve_name' => $s->sleeve_name,
                            'image' => $this->publicImage($s->image),
                        ]),

                        'linings' => $body->linings->map(fn ($l) => [
                            'id' => $l->id,
                            'lining_name' => $l->lining_name,
                            'image' => $this->publicImage($l->image),
                        ]),

                        'bodyButtons' => $body->bodyButtons->map(fn ($b) => [
                            'id' => $b->id,
                            'button_name' => $b->button_name,
                            'image' => $this->publicImage($b->image),
                        ]),

                        'sidePockets' => $body->sidePockets->map(function ($p) {
                            return [
                                'id' => $p->id,
                                'side_pocket_name' => $p->side_pocket_name,
                                'image' => $this->publicImage($p->image),

                                'sidePocketButtons' => $p->sidePocketButtons->map(fn ($b) => [
                                    'id' => $b->id,
                                    'button_name' => $b->button_name,
                                    'image' => $this->publicImage($b->image),
                                ]),
                            ];
                        }),

                        'chestPockets' => $body->chestPockets->map(function ($p) {
                            return [
                                'id' => $p->id,
                                'chest_pocket_name' => $p->chest_pocket_name,
                                'image' => $this->publicImage($p->image),

                                'chestPocketButtons' => $p->chestPocketButtons->map(fn ($b) => [
                                    'id' => $b->id,
                                    'button_name' => $b->button_name,
                                    'image' => $this->publicImage($b->image),
                                ]),
                            ];
                        }),

                        'lapels' => $body->lapels->map(function ($l) {
                            return [
                                'id' => $l->id,
                                'lapel_name' => $l->lapel_name,
                                'image' => $this->publicImage($l->image),

                                'subcategories' => $l->subcategories->map(fn ($s) => [
                                    'id' => $s->id,
                                    'subcategory_name' => $s->subcategory_name,
                                    'image' => $this->publicImage($s->image),
                                ]),
                            ];
                        }),
                    ];
                }),
            ];
        });

        return Inertia::render('SuitConfigurator/Index', [
            'fabrics' => $fabrics,
        ]);
    }

    private function publicImage(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
