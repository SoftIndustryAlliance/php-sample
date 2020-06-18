<?php

namespace App\Import\DTO;

use Carbon\Carbon;

/**
 * Cup data transfer object.
 *
 * @package App\Import\DTO
 */
class Cup extends DTO
{

    /**
     * @var Carbon
     */
    private $date;

    /**
     * @var bool
     */
    private $free;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Seller
     */
    private $seller;

    /**
     * Cup constructor.
     */
    public function __construct(Client $client, Seller $seller)
    {
        $this->free = false;
        $this->date = new Carbon();
        $this->client = $client;
        $this->seller = $seller;
    }

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /**
     * @param Carbon $date
     *
     * @return $this
     */
    public function setDate(Carbon $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFree(): bool
    {
        return $this->free;
    }

    /**
     * @param bool $free
     *
     * @return $this
     */
    public function setFree(bool $free): self
    {
        $this->free = $free;
        return $this;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return Seller
     */
    public function getSeller(): Seller
    {
        return $this->seller;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return md5(
            $this->seller->getId()
            . $this->client->getId()
            . $this->getDate()->timestamp
            . $this->free
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'date' => $this->getDate()->format('Y-m-d H:i:s'),
            'free' => $this->free,
            'seller' => $this->seller->toArray(),
            'client' => $this->client->toArray(),
        ];
    }
}
