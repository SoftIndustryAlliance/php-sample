<?php

namespace App\Import\IdMap;

use App\Import\DTO\DTO;
use Illuminate\Database\Eloquent\Model;

/**
 * IdMap repository.
 *
 * @package App\Import\IdMap
 */
interface IdMapRepository
{

    /**
     * Finds a mapped model by DTO.
     *
     * @param DTO $dto
     *
     * @return Model|null The mapped model or null when no mapping exists.
     */
    public function find(DTO $dto): ?Model;

    /**
     * Deletes a mapping by the DTO.
     *
     * @param DTO $dto
     *
     * @return bool
     */
    public function deleteByDTO(DTO $dto): bool;

    /**
     * Deletes a mapping by the model.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function deleteByModel(Model $model): bool;

    /**
     * Saves a mapping between the model and the DTO.
     *
     * @param Model $model
     * @param DTO $dto
     *
     * @return bool
     */
    public function save(Model $model, DTO $dto): bool;
}
