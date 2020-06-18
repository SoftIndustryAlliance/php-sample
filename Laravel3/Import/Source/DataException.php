<?php

namespace App\Import\Source;

/**
 * Data Source exception.
 *
 * @package App\Import\Source
 */
class DataException extends \Exception
{

    /**
     * @var DataSource
     */
    private $source;

    public function __construct(DataSource $source, string $message)
    {
        parent::__construct($message);
        $this->source = $source;
    }

    /**
     * @return DataSource
     */
    public function getDataSource(): DataSource
    {
        return $this->source;
    }
}
