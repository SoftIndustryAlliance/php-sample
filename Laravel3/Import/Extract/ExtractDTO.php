<?php

namespace App\Import\Extract;

use App\Import\Source\DataSource;
use Generator;

/**
 * Extracts DTOs from the data source.
 *
 * @package App\Import\Extract
 */
interface ExtractDTO
{

    /**
     * @param DataSource $source
     */
    public function setSource(DataSource $source);

    /**
     * Adds a field filter.
     *
     * @param string $field The field name.
     * @param string $value The value.
     */
    public function addFilter(string $field, string $value);

    /**
     * Checks is filter using for the specified field.
     *
     * @param string $field The field name,
     *
     * @return bool
     */
    public function canFilter(string $field): bool;

    /**
     * Is the value filtered ?
     *
     * @param string $field
     * @param string $value
     *
     * @return bool
     */
    public function isFiltered(string $field, string $value): bool;

    /**
     * @return DataSource
     */
    public function getSource(): ?DataSource;

    /**
     * Extracts a DTO from the data source.
     *
     * @return Generator
     * @throws ExtractException
     */
    public function getDTO(): Generator;
}
