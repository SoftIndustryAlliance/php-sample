<?php

namespace App\Import\Loader;

use App\Import\DTO\DTO;
use Illuminate\Database\Eloquent\Model;

/**
 * Data transfer object to database model loader.
 *
 * @package App\Import\Loader
 */
interface ModelLoader
{

    /**
     * Creates a new model and loads the DTO data into the model.
     *
     * @param DTO $dto
     *
     * @return Model
     * @throws LoaderException When DTO cannot be loaded into the model.
     */
    public function toModel($dto);

    /**
     * Synchronize the model's data with the DTO's.
     *
     * @param Model $model
     * @param DTO $dto
     *
     * @return void
     */
    public function syncModel($model, $dto);

    /**
     * Tries to find a real model in the repo(db) by the DTO.
     *
     * @param DTO $dto
     *
     * @return Model|null
     */
    public function findModel($dto);
}
