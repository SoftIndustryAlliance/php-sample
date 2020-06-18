<?php

namespace App\Import\Extract;

use App\Import\DTO\GeoCoords;
use App\Import\DTO\Shop;
use Astrotomic\Translatable\Locales;
use Generator;

/**
 * Extracts shops from the JSON data source.
 *
 * @package App\Import
 */
class JsonShops extends JsonDataExtractor
{
    /**
     * @var Locales
     */
    protected $locales;

    public function __construct(Locales $locales)
    {
        $this->locales = $locales;
    }

    /**
     * @inheritDoc
     */
    public function getDTO(): Generator
    {
        $data = $this->getData();
        if (!isset($data->Shops)) {
            throw new ExtractException('Cannot find shops data.');
        }
        $defaultLocale = $this->locales->current();
        foreach ($data->Shops as $idx => $shop) {
            $dto = new Shop($this->locales);
            $geo = new GeoCoords($shop->latLng->latitude, $shop->latLng->longitude);
            $dto->setCoords($geo);
            foreach ($this->locales->all() as $locale) {
                $name = $descr = '';
                if ($locale == $defaultLocale) {
                    $name = $shop->name;
                    $descr = $shop->descr;
                } else {
                    $key = "Shops_$locale";
                    if (isset($data->$key)) {
                        $name = $data->$key[$idx]->name ?? '';
                        $descr = $data->$key[$idx]->descr ?? '';
                    }
                }
                if (!empty($name)) {
                    $dto->setName($locale, $name);
                }
                if (!empty($descr)) {
                    $dto->setDescription($locale, $descr);
                }
            }
            $dto->setLogoUrl($shop->thumbnail);
            yield $dto;
        }
    }
}
