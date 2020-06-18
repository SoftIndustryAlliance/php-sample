<?php

namespace App\Import\DTO;

/**
 * Seller user data transfer object.
 *
 * @package App\Import\DTO
 */
class Seller extends User
{

    /**
     * @var Shop[]
     */
    private $shops = [];

    /**
     * @param Shop $shop
     *
     * @return $this
     */
    public function addShop(Shop $shop): self
    {
        $this->shops[] = $shop;
        return $this;
    }

    /**
     * @return Shop[]
     */
    public function getShops(): array
    {
        return $this->shops;
    }
}
