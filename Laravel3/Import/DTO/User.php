<?php

namespace App\Import\DTO;

/**
 * Abstract user DTO.
 *
 * @package App\Import\DTO
 */
abstract class User extends DTO
{

    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $email = '';

    /**
     * @var string
     */
    private $name = '';

    public function __construct(string $id)
    {
        $this->userId = $id;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        // TODO: validate email ?
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->userId,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
