<?php

namespace App\Import\DTO;

/**
 * The import Data Transfer Object.
 *
 * @package App\Import\DTO
 */
abstract class DTO
{

    /**
     * @return string
     */
    abstract public function getId(): string;

    /**
     * @return array
     */
    abstract public function toArray(): array;
}
