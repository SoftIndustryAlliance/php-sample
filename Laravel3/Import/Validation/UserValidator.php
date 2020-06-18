<?php

namespace App\Import\Validation;

use App\Import\DTO\DTO;
use Illuminate\Support\Facades\Validator;

/**
 * AuthUser DTO validator.
 *
 * @author skoro
 */
class UserValidator extends BaseDataValidator
{

    /**
     * @inheritDoc
     *
     * @param \App\Import\DTO\AuthUser $dto
     */
    public function validate(DTO $dto): void
    {
        parent::validate($dto);
        $validate = Validator::make($dto->toArray(), [
            'id' => 'required',
            'name' => 'required',
            'email' => 'email',
            'provider' => 'required',
            'socialId' => 'required',
        ]);
        if ($validate->fails()) {
            throw new ValidateException($dto, $validate->errors()->first());
        }
    }
}
