<?php

namespace App\Import\Loader;

use App\Classes\Constants;
use App\Import\DTO\AuthUser;
use App\Models\User;
use App\Support\CreateEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * AuthUser DTO loader.
 *
 * @author skoro
 */
class UserLoader implements ModelLoader
{
    use CreateEmail;

    /**
     * @param AuthUser $dto
     *
     * @return User
     */
    public function toModel($dto): Model
    {
        $user = new User();
        $user->name = $dto->getName();
        $email = $dto->getEmail();
        if (empty($email)) {
            $email = $this->createProviderEmail($dto->getProvider(), $dto->getSocialId());
        }
        $user->email = $email;
        $user->role_id = Constants::ROLES['Client'];
        $user->is_active = true;
        $user->password = Hash::make(Str::random());
        $user->firebase_id = $dto->getId();
        return $user;
    }

    /**
     * @param User $model
     * @param AuthUser $dto
     */
    public function syncModel($model, $dto)
    {
        $email = $dto->getEmail();
        if (empty($email)) {
            $email = $this->createProviderEmail($dto->getProvider(), $dto->getSocialId());
        }
        $model->email = $email;
        $model->name = $dto->getName();
        $model->firebase_id = $dto->getId();
    }
    
    /**
     * @param AuthUser $dto
     *
     * @return User|null
     */
    public function findModel($dto)
    {
        $email = $dto->getEmail();
        if (empty($email)) {
            $user = User::where('firebase_id', $dto->getId())->first();
        } else {
            $user = User::where('email', $email)->first();
        }
        if ($user) {
            if (! $user->isClient()) {
                throw new LoaderException($dto, "User ({$user->name}) found but it is not a client.");
            }
            return $user;
        }
    }
}
