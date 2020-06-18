<?php

namespace App\Import;

/**
 * Class MissingLocaleException
 *
 * @package App\Import
 */
class MissingLocaleException extends \Exception
{
    public function __construct(string $locale)
    {
        parent::__construct($this->createMessage($locale));
    }

    protected function createMessage(string $locale): string
    {
        return "Locale is missing: $locale";
    }
}
