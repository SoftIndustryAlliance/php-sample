<?php

namespace App\Import\Extract;

use App\Import\DTO\Client;
use App\Import\DTO\Cup;
use App\Import\DTO\Seller;
use Carbon\Carbon;
use Generator;

/**
 * Extracts cups from the JSON data source.
 *
 * @package App\Import\Extract
 */
class JsonCups extends JsonDataExtractor
{

    /**
     * @inheritDoc
     */
    public function getDTO(): Generator
    {
        $data = $this->getData();
        if (empty($data->CupsClient)) {
            throw new ExtractException('Cannot find cups client data.');
        }

        if (empty($data->Sellers)) {
            throw new ExtractException('Sellers data is needed for getting cups.');
        }

        if (empty($data->Clients)) {
            throw new ExtractException('Clients data is needed for getting cups.');
        }

        foreach ($data->CupsClient as $clientId => $cupsData) {
            if ($this->canFilter('client') && !$this->isFiltered('client', $clientId)) {
                continue;
            }
            foreach ($cupsData as $name => $cups) {
                $isFree = $name == 'cups_free_received';
                foreach ($cups as $cup) {
                    $client = $this->findClient($data->Clients, $clientId);
                    if (isset($cup->selleruid)) {
                        $seller = $this->findSeller($data->Sellers, $cup->selleruid);
                    }
                    if (isset($cup->date) && $client && $seller) {
                        $date = Carbon::createFromTimestampMs($cup->date);
                        $dto = new Cup($client, $seller);
                        $dto->setFree($isFree);
                        $dto->setDate($date);
                        yield $dto;
                    } else {
                        // TODO: log ?
                    }
                }
            }
        }
    }

    /**
     * Finds a client by Id.
     *
     * @param \stdClass[] $clients  The list of clients.
     * @param string      $clientId
     *
     * @return Client|null
     */
    protected function findClient($clients, string $clientId): ?Client
    {
        foreach ($clients as $id => $data) {
            if ($id == $clientId) {
                $client = new Client($id);
                $client->setName($data->name);
                $client->setEmail($data->email);
                return $client;
            }
        }
        return null;
    }

    /**
     * Finds a seller by Id.
     *
     * @param \stdClass[] $sellers  The list of sellers.
     * @param string      $sellerId
     *
     * @return Seller|null
     */
    protected function findSeller($sellers, string $sellerId): ?Seller
    {
        foreach ($sellers as $id => $data) {
            if ($id == $sellerId) {
                $seller = new Seller($id);
                $seller->setName($data->name);
                $seller->setEmail($data->email);
                return $seller;
            }
        }
        return null;
    }
}
