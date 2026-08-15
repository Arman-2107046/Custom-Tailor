<?php

namespace App\Filament\Resources\Lapels\Pages;

use App\Filament\Imports\LapelImporter;
use App\Filament\Resources\Lapels\LapelResource;
use App\Models\Body;
use App\Models\BodyType;
use App\Models\Fabric;
use App\Models\Lapel;
use App\Models\LapelCategory;
use App\Models\LapelSubCategory;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLapels extends ListRecords
{
    protected static string $resource = LapelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            ImportAction::make()
                ->importer(LapelImporter::class),

            Action::make('bulkUpload')
                ->label('Bulk Upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalWidth('lg')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn () => view(
                    'filament.modals.bulk-upload-images',
                    [
                        'cloudName' => $this->getCloudinaryCloudName(),
                        'uploadPreset' => env('CLOUDINARY_UPLOAD_PRESET', ''),
                        'title' => 'Upload Lapel Images',
                        'subtitle' => 'Drag & drop lapel images here, or click to browse',
                        'filenameHint' => 'SB1_Peak_Wide_Blue Stripe[_1].png',
                        'wireMethod' => 'processUploads',
                    ]
                )),
        ];
    }

    /**
     * Filename format:
     *
     * SB1_Peak_Wide_Blue Stripe.png
     *
     * Default:
     *
     * SB1_Peak_Wide_Blue Stripe_1.png
     *
     * Structure:
     *
     * BODYTYPECODE_CATEGORY_SUBCATEGORY_FABRIC
     */
    public function processUploads(array $files): void
    {
        $imported = 0;
        $failed = [];

        /*
         * Build lookup maps.
         */

        // BodyType is identified by code.
        $bodyTypeMap = BodyType::pluck('id', 'code')->toArray();

        // Lapel category is identified by name.
        $categoryMap = LapelCategory::pluck('id', 'name')->toArray();

        // Subcategory is identified by name.
        $subCategoryMap = LapelSubCategory::pluck('id', 'name')->toArray();

        // Fabric is identified by name.
        $fabricMap = Fabric::pluck('id', 'name')->toArray();

        foreach ($files as $file) {

            $originalName = $file['name'] ?? null;
            $url = $file['url'] ?? null;

            if (! $originalName || ! $url) {
                $failed[] = 'Invalid file data';
                continue;
            }

            /*
             * Remove extension.
             */
            $filename = trim(
                pathinfo($originalName, PATHINFO_FILENAME)
            );

            if ($filename === '') {
                $failed[] = $originalName . ' — invalid filename';
                continue;
            }

            /*
             * Split filename.
             */
            $parts = explode('_', $filename);

            /*
             * Required:
             *
             * SB1_Peak_Wide_Blue Stripe
             *
             * 4 parts.
             *
             * Default:
             *
             * SB1_Peak_Wide_Blue Stripe_1
             *
             * 5 parts.
             */
            if (count($parts) < 4 || count($parts) > 5) {
                $failed[] = $originalName . ' — invalid filename format';
                continue;
            }

            $bodyCode = trim($parts[0]);
            $categoryName = trim($parts[1]);
            $subCategoryName = trim($parts[2]);

            /*
             * Determine whether this is the default lapel.
             */
            $isDefault = false;

            if (count($parts) === 5) {

                if (trim($parts[4]) !== '1') {
                    $failed[] = $originalName . ' — invalid default suffix';
                    continue;
                }

                $isDefault = true;
            }

            /*
             * Fabric name.
             */
            $fabricName = trim($parts[3]);

            if (
                $bodyCode === '' ||
                $categoryName === '' ||
                $subCategoryName === '' ||
                $fabricName === ''
            ) {
                $failed[] = $originalName . ' — missing filename component';
                continue;
            }

            /*
             * Find BodyType by code.
             */
            $bodyTypeId = $bodyTypeMap[$bodyCode] ?? null;

            if (! $bodyTypeId) {
                $failed[] = $originalName
                    . ' — body type code not found: '
                    . $bodyCode;

                continue;
            }

            $bodyType = BodyType::find($bodyTypeId);

            if (! $bodyType) {
                $failed[] = $originalName
                    . ' — body type not found: '
                    . $bodyCode;

                continue;
            }

            /*
             * Find Fabric.
             */
            $fabricId = $fabricMap[$fabricName] ?? null;

            if (! $fabricId) {
                $failed[] = $originalName
                    . ' — fabric not found: '
                    . $fabricName;

                continue;
            }

            /*
             * Find Body.
             *
             * Body is identified using:
             *
             * body_type_id
             * fabric_id
             */
            $body = Body::where('body_type_id', $bodyType->id)
                ->where('fabric_id', $fabricId)
                ->first();

            if (! $body) {
                $failed[] = $originalName
                    . ' — body not found for '
                    . $bodyType->name
                    . ' + '
                    . $fabricName;

                continue;
            }

            $bodyId = $body->id;

            /*
             * Find Lapel Category.
             */
            $categoryId = $categoryMap[$categoryName] ?? null;

            if (! $categoryId) {
                $failed[] = $originalName
                    . ' — lapel category not found: '
                    . $categoryName;

                continue;
            }

            /*
             * Find Lapel Subcategory.
             */
            $subCategoryId = $subCategoryMap[$subCategoryName] ?? null;

            if (! $subCategoryId) {
                $failed[] = $originalName
                    . ' — lapel subcategory not found: '
                    . $subCategoryName;

                continue;
            }

            /*
             * Create / update Lapel.
             */
            Lapel::updateOrCreate(
                [
                    'body_id' => $bodyId,
                    'fabric_id' => $fabricId,
                    'lapel_category_id' => $categoryId,
                    'lapel_subcategory_id' => $subCategoryId,
                ],
                [
                    'image' => $url,
                    'is_default' => $isDefault,
                    'layer_index' => 150,
                    'status' => true,
                ]
            );

            $imported++;
        }

        /*
         * Notification.
         */
        Notification::make()
            ->title('Lapels imported')
            ->body(
                "{$imported} lapel"
                . ($imported === 1 ? '' : 's')
                . " imported."
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

    /**
     * Get Cloudinary cloud name.
     */
    private function getCloudinaryCloudName(): string
    {
        $cloud = env('CLOUDINARY_CLOUD_NAME', '');

        if (empty($cloud)) {

            $cloudinaryUrl = env('CLOUDINARY_URL');

            if ($cloudinaryUrl) {

                $cloud = parse_url(
                    $cloudinaryUrl,
                    PHP_URL_HOST
                ) ?? '';
            }
        }

        return $cloud;
    }
}
