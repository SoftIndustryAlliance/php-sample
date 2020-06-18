<?php

namespace App\Import\Loader;

use App\Import\DTO\AuthUser;
use App\Import\DTO\Cup;
use App\Import\IdMap\IdMapRepository;
use App\Models\Purchase;

/**
 * Cup DTO loader.
 *
 * @package App\Import\Loader
 */
class CupLoader implements ModelLoader
{

    /**
     * @var IdMapRepository
     */
    protected $idMap;

    public function __construct(IdMapRepository $idMap)
    {
        $this->idMap = $idMap;
    }

    /**
     * @param Cup $dto
     *
     * @return Purchase
     */
    public function toModel($dto)
    {
        // Cup client is a social user.
        $authDto = new AuthUser($dto->getClient()->getId(), '');
        $client = $this->idMap->find($authDto);
        if (empty($client)) {
            throw new LoaderException($dto, 'client must be imported');
        }
        $seller = $this->idMap->find($dto->getSeller());
        if (empty($seller)) {
            throw new LoaderException($dto, 'seller must be imported');
        }
        $purchase = new Purchase();
        $purchase->seller_id = $seller->id;
        $purchase->client_id = $client->id;
        $purchase->purchased = 1;
        $purchase->is_free = $dto->isFree();
        $purchase->created_at = $dto->getDate();
        return $purchase;
    }

    /**
     * @inheritDoc
     */
    public function syncModel($model, $dto)
    {
        // Nothing to synchronize.
    }

    /**
     * @inheritDoc
     */
    public function findModel($dto)
    {
        // Cup DTO is intended to be imported only once.
    }
}
