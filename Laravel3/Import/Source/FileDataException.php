<?php

namespace App\Import\Source;

/**
 * File source exception.
 *
 * @package App\Import\Source
 */
class FileDataException extends DataException
{
    public function __construct(FileSource $source, string $message)
    {
        parent::__construct($source, $message);
    }

    public function __toString()
    {
        return $this->getDataSource()->getFilename() . ': ' . $this->getMessage();
    }
}
