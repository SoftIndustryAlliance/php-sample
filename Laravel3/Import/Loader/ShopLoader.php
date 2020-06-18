<?php

namespace App\Import\Loader;

use App\Import\ImageUpload;
use App\Models\Shop as ShopModel;
use App\Import\DTO\Shop as ShopDTO;

/**
 * Shop DTO loader.
 *
 * @package App\Import\Loader
 */
class ShopLoader implements ModelLoader
{

    /**
     * @var ImageUpload
     */
    private $imageUpload;

    public function __construct(ImageUpload $imageUpload)
    {
        $this->imageUpload = $imageUpload;
    }

    /**
     * @param ShopDTO $dto
     * @return ShopModel
     */
    public function toModel($dto)
    {
        $shopModel = new ShopModel();
        $this->syncModel($shopModel, $dto);
        $shopModel->status = ShopModel::STATUS_ENABLED;
        return $shopModel;
    }

    /**
     * @param ShopModel $model
     * @param ShopDTO $dto
     *
     * @throws \App\Import\MissingLocaleException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function syncModel($model, $dto)
    {
        foreach ($dto->getNameLocales() as $locale) {
            $model->translateOrNew($locale)->name = $dto->getName($locale);
        }
        foreach ($dto->getDescriptionLocales() as $locale) {
            $model->translateOrNew($locale)->description = $dto->getDescription($locale);
        }
        $model->lat = $dto->getGeoCoords()->getLatitude();
        $model->lng = $dto->getGeoCoords()->getLongitude();
        $logoUrl = $dto->getLogoUrl();
        if (!empty($logoUrl)) {
            $file = $this->imageUpload->upload($logoUrl);
            if ($model->file_id != $file->id) {
                $model->file_id = $file->id;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function findModel($dto)
    {
        return null;
    }
}
