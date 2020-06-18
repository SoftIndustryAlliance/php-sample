<?php

namespace App\Import\Validation;

use App\Import\DTO\DTO;

/**
 * Validate exception.
 *
 * @package App\Import\Validation
 */
class ValidateException extends \Exception
{

    /**
     * @var DTO
     */
    private $dto;

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
