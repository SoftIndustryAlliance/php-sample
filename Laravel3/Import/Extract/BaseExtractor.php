<?php

namespace App\Import\Extract;

use App\Import\Source\DataSource;
use Generator;

/**
 * Base DTO extractor.
 *
 * @package App\Import\Extract
 */
abstract class BaseExtractor implements ExtractDTO
{

    /**
     * @var DataSource
     */
    private $source;

    /**
     * @var array
     */
    private $filter = [];

    /**
     * @inheritDoc
     */
    public function setSource(DataSource $source)
    {
        $this->source = $source;
    }

    /**
     * @inheritDoc
     */
    public function getSource(): ?DataSource
    {
        return $this->source;
    }

    /**
     * @inheritDoc
     */
    abstract public function getDTO(): Generator;

    /**
     * @inheritDoc
     */
    public function addFilter(string $field, string $value)
    {
        if (!isset($this->filter[$field])) {
            $this->filter[$field] = [];
        }
        $this->filter[$field][] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function canFilter(string $field): bool
    {
        return isset($this->filter[$field]);
    }

    /**
     * @inheritDoc
     */
    public function isFiltered(string $field, string $value): bool
    {
        if (isset($this->filter[$field])) {
            return in_array($value, $this->filter[$field]);
        }
        return false;
    }
}
