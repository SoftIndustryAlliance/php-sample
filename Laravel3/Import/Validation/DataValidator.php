<?php

namespace App\Import\Validation;

use App\Import\DTO\DTO;

/**
 * DTO validator.
 *
 * @package App\Import\Validation
 */
interface DataValidator
{

    /**
     * Validates a DTO.
     *
     * @param DTO $dto
     *
     * @throws ValidateException When the DTO validation is failed.
     */
    public function validate(DTO $dto): void;
}
