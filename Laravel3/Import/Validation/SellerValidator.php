<?php

namespace App\Import\Validation;

use App\Import\DTO\DTO;
use Illuminate\Support\Facades\Validator;

/**
 * Seller DTO validator.
 *
 * @package App\Import\Validation
 */
class SellerValidator extends BaseDataValidator
{

    /**
     * @inheritDoc
     *
     * @param \App\Import\DTO\Seller $dto
     */
    public function validate(DTO $dto): void
    {
        parent::validate($dto);
        $data = [
            'email' => $dto->getEmail(),
        ];
        $validate = Validator::make($data, [
            'email' => 'required|email',
        ]);
        if ($validate->fails()) {
            throw new ValidateException($dto, $validate->errors()->first());
        }
    }
}
