<?php

namespace App\Import\DTO;

use App\Import\MissingLocaleException;
use Astrotomic\Translatable\Locales;

/**
 * Shop data transfer object.
 *
 * @package App\Import\DTO
 */
class Shop extends DTO
{
    /**
     * @var Locales
     */
    private $locales;

    /**
     * @var string[]
     */
    private $name = [];

    /**
     * @var string[]
     */
    private $desc = [];

    /**
     * @var GeoCoords
     */
    private $coords;

    /**
     * @var string
     */
    private $logoUrl;

    /**
     * Shop constructor.
     *
     * @param Locales $locales Translatable locales dependency.
     */
    public function __construct(Locales $locales)
    {
        $this->locales = $locales;
    }

    /**
     * Sets the shop name.
     *
     * @param string $locale
     * @param string $name
     *
     * @return $this
     * @throws MissingLocaleException
     */
    public function setName(string $locale, string $name): self
    {
        $this->name[$this->getLocale($locale)] = $name;
        return $this;
    }

    /**
     * Gets the shop name.
     *
     * @param string $locale
     * @param string $default The default name when no name for the specified locale.
     *
     * @return string
     * @throws MissingLocaleException
     */
    public function getName(string $locale = '', string $default = ''): string
    {
        return $this->name[$this->getLocale($locale)] ?? $default;
    }

    /**
     * Returns a locales list used in 'name' field.
     *
     * @return string[]
     */
    public function getNameLocales(): array
    {
        return array_keys($this->name);
    }

    /**
     * Sets the shop description.
     *
     * @param string $locale
     * @param string $desc
     *
     * @return $this
     * @throws MissingLocaleException
     */
    public function setDescription(string $locale, string $desc): self
    {
        $this->desc[$this->getLocale($locale)] = $desc;
        return $this;
    }

    /**
     * Gets the shop description.
     *
     * @param string $locale
     * @param string $default The default description when no description for
     *                        the specified locale.
     *
     * @return string
     * @throws MissingLocaleException
     */
    public function getDescription(string $locale, string $default = ''): string
    {
        return $this->desc[$this->getLocale($locale)] ?? $default;
    }

    /**
     * Returns a locales list used in 'description' field.
     *
     * @return string[]
     */
    public function getDescriptionLocales(): array
    {
        return array_keys($this->desc);
    }

    /**
     * Checks that the locale is presented.
     *
     * @param string $locale
     *
     * @return string
     * @throws MissingLocaleException
     */
    protected function getLocale(string $locale = ''): string
    {
        if (empty($locale)) {
            $locale = $this->locales->current();
        }
        if (! $this->locales->has($locale)) {
            throw new MissingLocaleException($locale);
        }
        return $locale;
    }

    /**
     * @return GeoCoords
     */
    public function getGeoCoords(): GeoCoords
    {
        return $this->coords;
    }

    /**
     * @param GeoCoords $coords
     * @return $this
     */
    public function setCoords(GeoCoords $coords): self
    {
        $this->coords = $coords;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogoUrl(): string
    {
        return $this->logoUrl;
    }

    /**
     * @param string $logoUrl
     * @return $this
     */
    public function setLogoUrl(string $logoUrl): self
    {
        // TODO: validate url ?
        $this->logoUrl = $logoUrl;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        $name = $this->getName();
        if (empty($name)) {
            throw new \RuntimeException("Couldn't create DTO Id - name is empty.");
        }
        return md5($name);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'desc' => $this->desc,
            'coords' => $this->coords,
        ];
    }
}
