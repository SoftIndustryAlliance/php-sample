<?php

namespace App\Import\Source;

/**
 * Data source.
 *
 * @package App\Import\Source
 */
interface DataSource
{

    /**
     * Reads the chunk of the data from the source.
     *
     * @return string|null The data or null when EOF is reached.
     * @throws DataException When the data cannot be read.
     */
    public function read(): ?string;

    /**
     * Can the source fetch the data ?
     *
     * @return bool
     */
    public function isEof(): bool;

    /**
     * Resets the source to its initial state.
     */
    public function reset(): void;
}
