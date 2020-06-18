<?php

namespace App\Import\IdMap;

use App\Import\DTO\DTO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Database implementation of IdMap repository.
 *
 * @package App\Import
 */
class IdMapRepositoryDB implements IdMapRepository
{

    /**
     * Id mapping table name.
     */
    const TABLE = 'import_map';

    /**
     * @inheritDoc
     */
    public function find(DTO $dto): ?Model
    {
        $map = $this->findMapQuery($dto)->first();
        if (empty($map)) {
            return null;
        }
        $class = $map->model_class;
        $model = $class::find($map->model_id);
        if (empty($model)) {
            $this->deleteByDTO($dto);
            return null;
        }
        return $model;
    }

    /**
     * Returns an initial table query.
     *
     * @return Builder
     */
    protected function getTable(): Builder
    {
        return DB::table(self::TABLE);
    }

    /**
     * Returns the find query.
     *
     * @param DTO $dto
     *
     * @return Builder
     */
    protected function findMapQuery(DTO $dto): Builder
    {
        return $this->getTable()
            ->where($this->getDTOParams($dto));
    }

    /**
     * Returns DTO params for using in WHERE queries.
     *
     * @param DTO $dto
     *
     * @return array
     */
    protected function getDTOParams(DTO $dto): array
    {
        return [
            'dto_class' => get_class($dto),
            'dto_id' => $dto->getId(),
        ];
    }

    /**
     * Returns Model params for using in WHERE queries.
     *
     * @param Model $model
     *
     * @return array
     */
    protected function getModelParams(Model $model): array
    {
        return [
            'model_class' => get_class($model),
            'model_id' => $model->id,
        ];
    }

    /**
     * @inheritDoc
     */
    public function deleteByDTO(DTO $dto): bool
    {
        return $this->findMapQuery($dto)->delete();
    }

    /**
     * @inheritDoc
     */
    public function deleteByModel(Model $model): bool
    {
        return $this->getTable()
            ->where($this->getModelParams($model))
            ->delete();
    }

    /**
     * @inheritDoc
     */
    public function save(Model $model, DTO $dto): bool
    {
        $modelClass = get_class($model);
        $modelId = $model->id;

        $map = $this->getTable()->where($this->getModelParams($model))->exists();
        $exist = $this->findMapQuery($dto)->first();

        // The model's side is unique but DTO's side can be different.
        if ($map && !$exist) {
            $this->deleteByModel($model);
        }

        if ($exist) {
            if ($exist->model_class != $modelClass || $exist->model_id != $modelId) {
                $this->findMapQuery($dto)->update($this->getModelParams($model));
            }
            return true;
        }
        $data = array_merge($this->getDTOParams($dto), $this->getModelParams($model));
        return $this->getTable()->insert($data);
    }
}
