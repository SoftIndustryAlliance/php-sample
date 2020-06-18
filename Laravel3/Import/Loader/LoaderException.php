<?php

namespace App\Import\Loader;

use App\Import\DTO\DTO;

/**
 * DTO loader exception.
 *
 * @package App\Import\Loader
 */
class LoaderException extends \Exception
{
    /**
     * @var DTO
     */
    private $dto;

    /**
     * LoaderException constructor.
     *
     * @param DTO $dto
     * @param string $message
     */
    public function __construct(DTO $dto, string $message)
    {
        parent::__construct($message);
        $this->dto = $dto;
    }

    public function getDTO(): DTO
    {
        return $this->dto;
    }
}
