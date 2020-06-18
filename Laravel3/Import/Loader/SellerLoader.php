<?php

namespace App\Import\Loader;

use App\Classes\Constants;
use App\Models\Seller;
use App\Models\User;
use App\Import\DTO\Seller as SellerDTO;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seller DTO loader.
 *
 * @package App\Import\Loader
 */
class SellerLoader implements ModelLoader
{

    /**
     * @param SellerDTO $dto
     *
     * @return Seller
     */
    public function toModel($dto)
    {
        $seller = new Seller();
        $seller->email = $dto->getEmail();
        $seller->name = $dto->getName();
        if (empty($seller->name)) {
            $seller->name = $this->createNameFromEmail($dto->getEmail());
        }
        $seller->role_id = Constants::ROLES['Seller'];
        $seller->is_active = true;
        $seller->password = Hash::make(Str::random());
        return $seller;
    }

    /**
     * Creates a unique name from the email.
     *
     * @param string $email
     *
     * @return string
     */
    protected function createNameFromEmail(string $email): string
    {
        [$name, ] = explode('@', $email, 2);
        do {
            $generated = $name . '-' . rand(1, 999999);
        } while (User::where('name', $generated)->exists());
        return $generated;
    }

    /**
     * @param Seller $model
     * @param SellerDTO $dto
     */
    public function syncModel($model, $dto)
    {
        $model->email = $dto->getEmail();
        $name = $dto->getName();
        if ($name) {
            $model->name = $name;
        }
    }

    /**
     * @param \App\Import\DTO\User $dto
     *
     * @return User|null
     */
    public function findModel($dto)
    {
        $email = $dto->getEmail();
        $user = Seller::where('email', $email)->first();
        if ($user) {
            if (! $user->isSeller()) {
                throw new LoaderException($dto, "User ({$user->id}) with email '$email' found but it is not seller.");
            }
            return $user;
        }
    }
}
