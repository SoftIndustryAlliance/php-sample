<?php

namespace App\Import\Validation;

use App\Import\DTO\DTO;

/**
 * Stub for the specific validators.
 *
 * @package App\Import\Validation
 */
class BaseDataValidator implements DataValidator
{

    /**
     * @inheritDoc
     */
    public function validate(DTO $dto): void
    {
    }
}
