<?php

namespace App\Import\DTO;

/**
 * Auth user DTO.
 *
 * @author skoro
 */
class AuthUser extends User
{
    /**
     * Social provider name.
     *
     * @var string
     */
    private $provider;
    
    /**
     * User's ID from the provider.
     *
     * @var string
     */
    private $socialId;
    
    public function __construct(string $localId, string $socialId)
    {
        parent::__construct($localId);
        $this->socialId = $socialId;
    }
    
    /**
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     *
     * @return $this
     */
    public function setProvider(string $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * @return string
     */
    public function getSocialId(): string
    {
        return $this->socialId;
    }
    
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'provider' => $this->provider,
            'socialId' => $this->socialId,
        ]);
    }
}
