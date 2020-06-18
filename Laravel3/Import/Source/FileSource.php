<?php

namespace App\Import\Source;

/**
 * File data source.
 *
 * @package App\Import\Source
 */
class FileSource implements DataSource
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var bool
     */
    private $eof = false;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @inheritDoc
     */
    public function read(): ?string
    {
        if ($this->eof) {
            return null;
        }
        $this->eof = true;
        if (!file_exists($this->filename)) {
            throw new FileDataException($this, 'file not found');
        }
        return file_get_contents($this->filename);
    }

    /**
     * @inheritDoc
     */
    public function isEof(): bool
    {
        return $this->eof;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @inheritDoc
     */
    public function reset(): void
    {
        $this->eof = false;
    }
}
