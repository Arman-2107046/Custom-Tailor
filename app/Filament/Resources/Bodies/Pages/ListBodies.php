<?php

namespace App\Filament\Resources\Bodies\Pages;

use App\Filament\Imports\BodyImporter;
use App\Filament\Resources\Bodies\BodyResource;
use App\Models\Body;
use App\Models\BodyType;
use App\Models\Fabric;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBodies extends ListRecords
{
    protected static string $resource = BodyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            ImportAction::make()
                ->importer(BodyImporter::class)
                ->label('Import Bodies'),

            Action::make('bulkUploadBodies')
                ->label('Bulk Upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalWidth('lg')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn () => view('filament.modals.bulk-upload-images', [
                    'cloudName' => $this->getCloudinaryCloudName(),
                    'uploadPreset' => env('CLOUDINARY_UPLOAD_PRESET', ''),
                    'title' => 'Upload Body Images',
                    'subtitle' => 'Drag & drop images here, or click to browse',
                    'filenameHint' => 'Code_Fabric[_1].png',
                    'wireMethod' => 'processUploads',
                ])),
        ];
    }

    /**
     * Filename formats:
     *
     * SB1_Blue Stripe.png
     * SB1_Blue Stripe_1.png
     * DB2_Navy.png
     * DB4_Black Wool.png
     * DB6_Grey_1.png
     *
     * _1 at the end = default body.
     * Status is always true.
     */
    public function processUploads(array $files): void
    {
        $imported = 0;
        $failed = [];

        $fabricMap = Fabric::pluck('id', 'name')->toArray();

        // Build lookup by code (SB1, DB2, etc.)
        $bodyTypes = BodyType::select('id', 'name', 'code')
            ->get()
            ->keyBy(fn ($bodyType) => strtoupper($bodyType->code));

        foreach ($files as $file) {

            $originalName = $file['name'] ?? null;
            $url = $file['url'] ?? null;

            if (! $originalName || ! $url) {
                continue;
            }

            $filename = pathinfo($originalName, PATHINFO_FILENAME);

            /*
             |--------------------------------------------------------------
             | Supported filenames
             |--------------------------------------------------------------
             | SB1_Black Wool.png
             | SB1_Black Wool_1.png
             | DB2_Navy.png
             | DB4_Grey Wool.png
             | DB6_Blue Stripe_1.png
             */

            $parts = explode('_', $filename);

            if (count($parts) < 2 || count($parts) > 3) {
                $failed[] = "{$originalName} — invalid filename";
                continue;
            }

            $bodyCode = strtoupper(trim($parts[0]));
            $fabricName = trim($parts[1]);

            // "_1" means default body
            $isDefault = isset($parts[2]) && $parts[2] === '1';

            $fabricId = $fabricMap[$fabricName] ?? null;

            if (! $fabricId) {
                $failed[] = "{$fabricName} — fabric not found";
                continue;
            }

            $bodyType = $bodyTypes->get($bodyCode);

            if (! $bodyType) {
                $failed[] = "{$bodyCode} — body type not found";
                continue;
            }

            if ($isDefault) {
                Body::where('fabric_id', $fabricId)
                    ->update([
                        'is_default' => false,
                    ]);
            }

            Body::updateOrCreate(
                [
                    'fabric_id' => $fabricId,
                    'body_type_id' => $bodyType->id,
                ],
                [
                    'image' => $url,
                    'layer_index' => 100,
                    'is_default' => $isDefault,
                    'status' => true,
                ]
            );

            $imported++;
        }

        Notification::make()
            ->title('Body import completed')
            ->body(
                "{$imported} body(s) imported."
                . (
                    count($failed)
                        ? ' Failed: ' . implode(', ', $failed)
                        : ''
                )
            )
            ->success(empty($failed))
            ->warning(! empty($failed))
            ->send();
    }

    private function getCloudinaryCloudName(): string
    {
        $cloud = env('CLOUDINARY_CLOUD_NAME', '');

        if (empty($cloud)) {
            $cloudinaryUrl = env('CLOUDINARY_URL');

            if ($cloudinaryUrl) {
                $cloud = parse_url($cloudinaryUrl, PHP_URL_HOST) ?? '';
            }
        }

        return $cloud;
    }
}
