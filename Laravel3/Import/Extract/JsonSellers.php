<?php

namespace App\Import\Extract;

use App\Import\DTO\Seller;
use App\Import\Source\DataSource;
use Generator;

/**
 * Extracts sellers from the JSON data source.
 *
 * JSON data has no information about the seller's shops. This extractor
 * just extracts all the shops from the data and attaches it to the seller.
 *
 * @package App\Import\Extract
 */
class JsonSellers extends JsonDataExtractor
{

    /**
     * @var JsonShops
     */
    protected $jsonShops;

    /**
     * JsonSellers constructor.
     *
     * @param JsonShops $jsonShops
     */
    public function __construct(JsonShops $jsonShops)
    {
        $this->jsonShops = $jsonShops;
    }

    /**
     * @inheritDoc
     */
    public function getDTO(): Generator
    {
        $data = $this->getData();
        if (empty($data->Sellers)) {
            throw new ExtractException('Cannot find sellers data.');
        }
        $this->getSource()->reset();
        $shops = $this->getShops();
        foreach ($data->Sellers as $id => $seller) {
            $dto = new Seller($id);
            $dto->setName($seller->name);
            $dto->setEmail($seller->email);
            foreach ($shops as $shop) {
                $dto->addShop($shop);
            }
            yield $dto;
        }
    }

    /**
     * @return \App\Import\DTO\Shop[]
     *
     * @throws ExtractException
     */
    protected function getShops(): array
    {
        $shops = [];
        foreach ($this->jsonShops->getDTO() as $shopDTO) {
            $shops[] = $shopDTO;
        }
        return $shops;
    }

    /**
     * @inheritDoc
     */
    public function setSource(DataSource $source)
    {
        parent::setSource($source);
        $this->jsonShops->setSource($source);
    }
}
