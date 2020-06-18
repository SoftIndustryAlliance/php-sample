<?php

namespace App\Import;

use App\Events\ImportAddedEvent;
use App\Events\ImportFailedEvent;
use App\Events\ImportUpdatedEvent;
use App\Import\DTO\DTO;
use App\Import\Extract\ExtractDTO;
use App\Import\IdMap\IdMapRepository;
use App\Import\Loader\ModelLoader;
use App\Import\Source\DataSource;
use App\Import\Validation\DataValidator;
use Illuminate\Database\Eloquent\Model;
use Exception;

/**
 * Import.
 *
 * @package App\Import
 */
class Import
{

    /**
     * @var ExtractDTO
     */
    private $extract;

    /**
     * @var ModelLoader
     */
    private $loader;

    /**
     * @var IdMapRepository
     */
    private $idMapRepository;

    /**
     * @var DataValidator
     */
    private $dataValidator;

    /**
     * Import constructor.
     *
     * @param ExtractDTO $extract
     * @param ModelLoader $loader
     * @param IdMapRepository $idMapRepository
     * @param DataValidator $dataValidator
     */
    public function __construct(
        ExtractDTO $extract,
        ModelLoader $loader,
        IdMapRepository $idMapRepository,
        DataValidator $dataValidator
    ) {
        $this->extract = $extract;
        $this->loader = $loader;
        $this->idMapRepository = $idMapRepository;
        $this->dataValidator = $dataValidator;
    }

    /**
     * @throws \App\Import\Extract\ExtractException
     */
    public function run()
    {
        foreach ($this->extract->getDTO() as $dto) {
            try {
                $this->dataValidator->validate($dto);
                $model = $this->idMapRepository->find($dto);
                if ($model) {
                    $this->loader->syncModel($model, $dto);
                    if ($model->isDirty() && $model->save()) {
                        $this->sendEventUpdated($model, $dto);
                    }
                } else {
                    $model = $this->loader->findModel($dto);
                    if ($model) {
                        $this->loader->syncModel($model, $dto);
                    } else {
                        $model = $this->loader->toModel($dto);
                    }
                    if ($model->save() && $this->idMapRepository->save($model, $dto)) {
                        $this->sendEventAdded($model, $dto);
                    }
                }
            } catch (Exception $e) {
                $this->sendEventFailed($e, $dto);
            }
        }
    }

    /**
     * @return DataSource
     */
    public function getSource(): DataSource
    {
        return $this->source;
    }

    /**
     * @return ExtractDTO
     */
    public function getExtract(): ExtractDTO
    {
        return $this->extract;
    }

    /**
     * @return ModelLoader
     */
    public function getLoader(): ModelLoader
    {
        return $this->loader;
    }

    /**
     * @param Model $model
     * @param DTO $dto
     *
     * @return array|null
     */
    protected function sendEventAdded(Model $model, DTO $dto)
    {
        return event(new ImportAddedEvent($model, $dto));
    }

    /**
     * @param Model $model
     * @param DTO $dto
     *
     * @return array|null
     */
    protected function sendEventUpdated(Model $model, DTO $dto)
    {
        return event(new ImportUpdatedEvent($model, $dto));
    }

    /**
     * @param Exception $exception
     * @param DTO $dto
     *
     * @return array|null
     */
    protected function sendEventFailed(Exception $exception, DTO $dto)
    {
        return event(new ImportFailedEvent($exception, $dto));
    }
}
