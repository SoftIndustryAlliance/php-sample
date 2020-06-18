<?php

namespace App\Import\Extract;

use App\Import\DTO\AuthUser;
use Generator;

/**
 * Extracts users from the JSON data source.
 *
 * @author skoro
 */
class JsonUsers extends JsonDataExtractor
{

    /**
     * @inheritDoc
     */
    public function getDTO(): Generator
    {
        $data = $this->getData();
        if (empty($data->users)) {
            throw new ExtractException('Cannot find users data.');
        }
        
        foreach ($data->users as $user) {
            if (!isset($user->providerUserInfo)) {
                continue;
            }
            $localId = $user->localId ?? '';
            if ($this->canFilter('id') && ! $this->isFiltered('id', $localId)) {
                continue;
            }
            $name = $user->displayName ?? '';
            $email = $user->email ?? '';
            if (empty($user->providerUserInfo)) {
                continue;
            }
            $providerInfo = $user->providerUserInfo[0];
            $provider = $this->getProviderName($providerInfo);
            $socialId = $providerInfo->rawId;
            if (empty($name)) {
                $name = $providerInfo->displayName;
            }
            $dto = new AuthUser($localId, $socialId);
            $dto->setName($name);
            $dto->setProvider($provider);
            $dto->setEmail($email);
            yield $dto;
        }
    }
    
    protected function getProviderName($info): string
    {
        switch ($info->providerId) {
            case 'google.com':
                return 'google';
            case 'facebook.com':
                return 'facebook';
        }
        return '';
    }
}
