<?php

namespace App\Import\DTO;

/**
 * Client data transfer object.
 *
 * @package App\Import\DTO
 */
class Client extends User
{

    /**
     * @var int
     */
    private $cups = 0;

    public function setCups(int $cups): self
    {
        $this->cups = $cups;
        return $this;
    }
}
