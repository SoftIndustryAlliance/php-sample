<?php

namespace App\Import\Extract;

use RuntimeException;

/**
 * Extract JSON data.
 *
 * @package App\Import\Extract
 */
abstract class JsonDataExtractor extends BaseExtractor
{
    /**
     * @var \stdClass The json data.
     */
    private $data;

    /**
     * Reads a json data from the data source.
     *
     * @return \stdClass
     * @throws ExtractException When the data source is reached end of file.
     * @throws \App\Import\Source\DataException
     */
    protected function getData()
    {
        $source = $this->getSource();
        if (empty($source)) {
            throw new RuntimeException('DataSource is not assigned.');
        }
        if (empty($this->data)) {
            if ($source->isEof()) {
                throw new ExtractException("Couldn't get data from source. Eof is reached.");
            }
            $this->data = json_decode($source->read());
        }
        return $this->data;
    }
}
