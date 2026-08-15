<?php

namespace App\Filament\Resources\SidePockets\Pages;

use App\Filament\Imports\SidePocketImporter;
use App\Filament\Resources\SidePockets\SidePocketResource;
use App\Models\Fabric;
use App\Models\SidePocket;
use App\Models\SidePocketType;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSidePockets extends ListRecords
{
    protected static string $resource = SidePocketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            ImportAction::make()
                ->importer(SidePocketImporter::class)
                ->label('Import Side Pockets'),

            Action::make('bulkUploadSidePockets')
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
                        'title' => 'Upload Side Pocket Images',
                        'subtitle' => 'Drag & drop side pocket images here, or click to browse',
                        'filenameHint' => 'CODE_Fabric[_1].png',
                        'wireMethod' => 'processUploads',
                        'accept' => 'image/*',
                    ]
                )),
        ];
    }

    /**
     * Filename format:
     *
     * WELT_Blue Wool.png
     * WELT_Blue Wool_1.png
     *
     * WELT = SidePocketType.code
     * Blue Wool = Fabric.name
     * _1 = default
     */
    public function processUploads(array $files): void
    {
        $imported = 0;
        $failed = [];

        /*
         * Build lookup maps.
         *
         * Side Pocket Type -> code
         * Fabric -> name
         */
        $sidePocketTypeMap = SidePocketType::pluck('id', 'code')->toArray();

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
             *
             * WELT_Blue Wool_1.png
             *
             * becomes:
             *
             * WELT_Blue Wool_1
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
             * CODE_Fabric
             *
             * 2 parts.
             *
             * Default:
             *
             * CODE_Fabric_1
             *
             * 3 parts.
             */
            if (count($parts) < 2 || count($parts) > 3) {
                $failed[] = $originalName . ' — invalid filename format';
                continue;
            }

            $typeCode = trim($parts[0]);
            $fabricName = trim($parts[1]);

            /*
             * Determine whether this is the default.
             */
            $isDefault = false;

            if (count($parts) === 3) {

                if (trim($parts[2]) !== '1') {
                    $failed[] = $originalName . ' — invalid default suffix';
                    continue;
                }

                $isDefault = true;
            }

            if (
                $typeCode === '' ||
                $fabricName === ''
            ) {
                $failed[] = $originalName . ' — missing filename component';
                continue;
            }

            /*
             * Find Side Pocket Type by CODE.
             *
             * Example:
             *
             * WELT
             * ->
             * SidePocketType
             */
            $sidePocketTypeId = $sidePocketTypeMap[$typeCode] ?? null;

            if (! $sidePocketTypeId) {
                $failed[] = $originalName
                    . ' — side pocket type code not found: '
                    . $typeCode;

                continue;
            }

            /*
             * Find Fabric by NAME.
             *
             * Example:
             *
             * Blue Wool
             * ->
             * Fabric
             */
            $fabricId = $fabricMap[$fabricName] ?? null;

            if (! $fabricId) {
                $failed[] = $originalName
                    . ' — fabric not found: '
                    . $fabricName;

                continue;
            }

            /*
             * Create / update Side Pocket.
             */
            SidePocket::updateOrCreate(
                [
                    'fabric_id' => $fabricId,
                    'side_pocket_type_id' => $sidePocketTypeId,
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
            ->title('Side pockets imported')
            ->body(
                "{$imported} side pocket"
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
